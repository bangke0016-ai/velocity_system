<?php
/** Model manajemen joki dan pembagian hasil. */
class Joki
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance()->getConnection();
    }

    public function all(string $search = '', string $keahlian = ''): array
    {
        $sql = 'SELECT * FROM joki WHERE 1 = 1';
        $params = [];
        if ($search !== '') { $sql .= ' AND nama LIKE ?'; $params[] = "%$search%"; }
        if ($keahlian !== '') { $sql .= ' AND keahlian = ?'; $params[] = $keahlian; }
        $stmt = $this->db->prepare($sql . ' ORDER BY status ASC, nama ASC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(string $nama, string $whatsapp, string $keahlian, float $rating = 0): int
    {
        $stmt = $this->db->prepare('INSERT INTO joki (nama, whatsapp, keahlian, status, rating, total_selesai, saldo_joki) VALUES (?, ?, ?, "aktif", ?, 0, 0)');
        $stmt->execute([$nama, $whatsapp, $keahlian, $rating]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $nama, string $whatsapp, string $keahlian, float $rating): bool
    {
        $stmt = $this->db->prepare('UPDATE joki SET nama = ?, whatsapp = ?, keahlian = ?, rating = ? WHERE id = ?');
        return $stmt->execute([$nama, $whatsapp, $keahlian, $rating, $id]);
    }

    public function toggleStatus(int $id): bool
    {
        return $this->db->prepare("UPDATE joki SET status = IF(status = 'aktif', 'suspend', 'aktif') WHERE id = ?")->execute([$id]);
    }

    /** Ubah status selesai dan bayarkan 70% secara atomik, tepat satu kali. */
    public function completeOrder(int $orderId): ?string
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT joki_id, total_price, status FROM orders WHERE id = ? FOR UPDATE');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            if (!$order || $order['status'] === 'selesai') { $this->db->commit(); return null; }
            if (empty($order['joki_id'])) { throw new RuntimeException('Order belum memiliki joki pengerja.'); }
            $earning = (int) floor((int) $order['total_price'] * JOKI_FEE_PERCENT / 100);
            $platformFee = (int) $order['total_price'] - $earning;
            $stmt = $this->db->prepare('UPDATE joki SET saldo_joki = saldo_joki + ?, total_selesai = total_selesai + 1 WHERE id = ? AND status = "aktif"');
            $stmt->execute([$earning, (int) $order['joki_id']]);
            if ($stmt->rowCount() !== 1) { throw new RuntimeException('Joki tidak ditemukan atau sedang disuspend.'); }
            $stmt = $this->db->prepare('UPDATE orders SET status = "selesai", status_alokasi = "selesai", platform_fee = ?, joki_earning = ?, payout_processed_at = NOW() WHERE id = ? AND status <> "selesai"');
            $stmt->execute([$platformFee, $earning, $orderId]);
            $stmt = $this->db->prepare("INSERT INTO histori_transaksi (id_pesanan, id_joki, total_bayar, bagian_admin, bagian_joki, tipe) VALUES (?, ?, ?, ?, ?, 'masuk')");
            $stmt->execute([$orderId, (int) $order['joki_id'], (int) $order['total_price'], $platformFee, $earning]);
            $payoutToken = bin2hex(random_bytes(32));
            $stmt = $this->db->prepare('INSERT INTO joki_payout_links (token, joki_id, order_id, amount, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 14 DAY))');
            $stmt->execute([$payoutToken, (int) $order['joki_id'], $orderId, $earning]);
            $this->db->commit();
            return $payoutToken;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    /** Konfirmasi transfer manual admin dan catat transaksi penarikan. */
    public function confirmWithdrawal(int $jokiId, ?string $proof = null): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT j.id, j.saldo_joki, p.id AS payout_id, p.nominal FROM joki j JOIN payout_joki p ON p.id_joki = j.id AND p.status = 'pending' WHERE j.id = ? ORDER BY p.id ASC LIMIT 1 FOR UPDATE");
            $stmt->execute([$jokiId]);
            $joki = $stmt->fetch();
            if (!$joki) { throw new RuntimeException('Joki tidak ditemukan.'); }
            $amount = min((int) $joki['nominal'], (int) $joki['saldo_joki']);
            if ($amount <= 0) { throw new RuntimeException('Permintaan penarikan tidak valid atau saldo tidak mencukupi.'); }
            $stmt = $this->db->prepare('UPDATE joki SET saldo_joki = saldo_joki - ?, withdrawal_amount = 0, withdrawal_requested = 0 WHERE id = ? AND saldo_joki >= ?');
            $stmt->execute([$amount, $jokiId, $amount]);
            if ($stmt->rowCount() !== 1) { throw new RuntimeException('Saldo joki berubah. Silakan ulangi.'); }
            $stmt = $this->db->prepare('UPDATE payout_joki SET status = "sukses", tanggal_bayar = NOW(), bukti_transfer = ? WHERE id = ? AND status = "pending"');
            $stmt->execute([$proof, (int) $joki['payout_id']]);
            $stmt = $this->db->prepare("INSERT INTO histori_transaksi (id_joki, total_bayar, bagian_admin, bagian_joki, tipe) VALUES (?, ?, 0, ?, 'tarik')");
            $stmt->execute([$jokiId, $amount, $amount]);
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }
}