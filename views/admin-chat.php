<?php $e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>
<style>
.chat-admin{display:grid;grid-template-columns:300px minmax(0,1fr);gap:18px}.chat-list,.chat-room{min-width:0}.chat-list .panel-head,.chat-room .panel-head{padding:18px 20px}.chat-list-items{max-height:620px;overflow:auto}.chat-list-item{display:block;padding:14px 18px;border-bottom:1px solid var(--line);transition:background .2s ease}.chat-list-item:hover,.chat-list-item.active{background:#eef8f8}.chat-list-item strong{display:block;overflow:hidden;color:var(--ink);font-size:13px;text-overflow:ellipsis;white-space:nowrap}.chat-list-item small{display:block;margin-top:5px;color:var(--muted);font-size:11px}.chat-preview{display:block;margin-top:7px;overflow:hidden;color:var(--muted);font-size:12px;text-overflow:ellipsis;white-space:nowrap}.chat-empty{padding:28px 18px;color:var(--muted);text-align:center}.chat-room-body{display:flex;min-height:500px;flex-direction:column;padding:20px}.chat-thread{display:flex;flex:1;flex-direction:column;gap:10px;max-height:500px;overflow:auto;padding:4px}.chat-bubble{max-width:min(75%,560px);padding:10px 13px;border-radius:14px;font-size:13px;line-height:1.5;white-space:pre-wrap}.chat-bubble.visitor{align-self:flex-start;background:#f1f5f9;color:var(--ink);border-bottom-left-radius:4px}.chat-bubble.admin{align-self:flex-end;background:var(--teal);color:#fff;border-bottom-right-radius:4px}.chat-bubble small{display:block;margin-top:5px;opacity:.7;font-size:10px}.chat-reply{display:flex;gap:9px;margin-top:18px;border-top:1px solid var(--line);padding-top:16px}.chat-reply textarea{min-height:45px;flex:1;resize:vertical;padding:11px 12px;border:1px solid var(--line);border-radius:9px;font:inherit}.chat-reply textarea:focus{outline:0;border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,124,131,.1)}@media(max-width:800px){.chat-admin{grid-template-columns:1fr}.chat-list-items{max-height:250px}.chat-room-body{min-height:430px}.chat-bubble{max-width:88%}}
.chat-bubble.assistant{align-self:flex-start;background:#eaf3ff;color:#163b72;border-bottom-left-radius:4px;}
</style>
<div class="chat-admin">
  <div class="panel chat-list">
    <div class="panel-head"><h2>Percakapan</h2></div>
    <div class="chat-list-items">
      <?php if (!$chatConversations): ?><div class="chat-empty">Belum ada pesan masuk.</div><?php endif; ?>
      <?php foreach ($chatConversations as $conversation): ?>
        <a class="chat-list-item <?= $selectedConversation === $conversation['conversation_token'] ? 'active' : '' ?>" href="admin.php?tab=chat&amp;conversation=<?= $e($conversation['conversation_token']) ?>">
          <strong>Pengunjung #<?= $e(substr($conversation['conversation_token'], 0, 8)) ?></strong>
          <small><?= $e(date('d M Y, H:i', strtotime($conversation['last_message_at']))) ?></small>
          <span class="chat-preview"><?= $e($conversation['last_message']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="panel chat-room">
    <div class="panel-head"><h2><?= $selectedConversation ? 'Detail percakapan' : 'Pilih percakapan' ?></h2></div>
    <?php if (!$selectedConversation): ?><div class="chat-empty">Pilih percakapan di sebelah kiri untuk membaca dan membalas pesan.</div>
    <?php else: ?><div class="chat-room-body"><div class="chat-thread">
      <?php foreach ($chatMessages as $message): ?><div class="chat-bubble <?= $message['sender'] === 'admin' ? 'admin' : ($message['sender'] === 'assistant' ? 'assistant' : 'visitor') ?>"><?= $e($message['message']) ?><small><?= $message['sender'] === 'admin' ? 'Admin' : ($message['sender'] === 'assistant' ? 'Velocity Assistant' : 'Pengunjung') ?> · <?= $e(date('d M Y, H:i', strtotime($message['created_at']))) ?></small></div><?php endforeach; ?>
    </div><form class="chat-reply" method="post"><input type="hidden" name="action" value="admin_chat_reply"><input type="hidden" name="csrf" value="<?= $e(adminCsrf()) ?>"><input type="hidden" name="conversation_token" value="<?= $e($selectedConversation) ?>"><textarea name="message" maxlength="2000" placeholder="Tulis balasan untuk pengunjung..." required></textarea><button class="btn" type="submit"><i class="fa-solid fa-paper-plane"></i> Kirim</button></form></div><?php endif; ?>
  </div>
</div>