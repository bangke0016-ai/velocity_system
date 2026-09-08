<?php
/**
 * Velocity System AI - Chat and FAQ widget
 * Include this file near the end of the page, before </body>.
 */
$faqs = [
    [
        'question' => 'Apakah identitas dan data pribadi saya aman?',
        'answer' => 'Jaminan 100% privasi aman dan rahasia terlindungi.',
    ],
    [
        'question' => 'Bagaimana jika tugas saya mendapatkan nilai jelek atau tidak sesuai instruksi?',
        'answer' => 'Kami memberikan garansi 100% revisi gratis hingga sesuai brief awal.',
    ],
    [
        'question' => 'Apakah pengerjaannya bisa selesai cepat untuk deadline darurat?',
        'answer' => 'Ya, kami memiliki layanan kilat dengan estimasi penyelesaian cepat sesuai tingkat kesulitan tugas.',
    ],
];
$chatEndpoint = defined('SITE_URL') ? SITE_URL . 'chat-api.php' : 'chat-api.php';
?>

<script>
    window.tailwind = window.tailwind || {};
    window.tailwind.config = {
        corePlugins: {
            preflight: false
        }
    };
</script>
<script src="https://cdn.tailwindcss.com"></script>
<style>
#velocity-chat-widget{position:fixed;right:28px;bottom:100px;z-index:9999}
#velocity-chat-panel.hidden{display:none!important}
#velocity-chat-widget [data-chat-open]{position:relative;display:flex;align-items:center;justify-content:center;width:60px;height:60px;border:3px solid rgba(255,255,255,.9);border-radius:999px;background:linear-gradient(145deg,#2563eb,#123b9b);color:#fff;cursor:pointer;box-shadow:0 12px 28px rgba(30,64,175,.28),0 0 0 0 rgba(37,99,235,.42);animation:velocity-chat-pulse 2.8s infinite;transition:transform .25s ease,box-shadow .25s ease}
#velocity-chat-widget [data-chat-open]:hover{transform:translateY(-4px) scale(1.04);box-shadow:0 18px 34px rgba(30,64,175,.36),0 0 0 8px rgba(37,99,235,.1);animation:none}
#velocity-chat-widget [data-chat-open] svg{filter:drop-shadow(0 2px 3px rgba(0,0,0,.2))}
#velocity-chat-panel{animation:velocity-chat-in .28s cubic-bezier(.22,1,.36,1)}
#velocity-chat-panel>div:first-child{position:relative;overflow:hidden;background:linear-gradient(135deg,#07152f 0%,#102f73 58%,#2563eb 100%);box-shadow:inset 0 -1px rgba(255,255,255,.12)}
#velocity-chat-panel>div:first-child:after{content:'';position:absolute;right:-34px;top:-50px;width:150px;height:150px;border:1px solid rgba(255,255,255,.15);border-radius:50%;box-shadow:0 0 0 18px rgba(255,255,255,.035),0 0 0 38px rgba(255,255,255,.025);pointer-events:none}
#velocity-chat-panel [data-chat-content]{scrollbar-width:thin;scrollbar-color:#93c5fd transparent}
#velocity-chat-messages{background:linear-gradient(180deg,#f8fbff,#f1f5f9)!important;border:1px solid #e2e8f0}
#velocity-chat-messages>div{animation:velocity-message-in .22s ease both}
#velocity-chat-form textarea{transition:border-color .2s ease,box-shadow .2s ease,background .2s ease}
#velocity-chat-form textarea:hover{background:#fbfdff}
#velocity-chat-form button{transition:transform .2s ease,box-shadow .2s ease}
#velocity-chat-form button:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 16px rgba(29,78,216,.22)}
.velocity-assistant-typing:after{content:'|';display:inline-block;margin-left:2px;color:#2563eb;animation:velocity-caret .8s steps(1,end) infinite}
@keyframes velocity-chat-pulse{0%,72%,100%{box-shadow:0 12px 28px rgba(30,64,175,.28),0 0 0 0 rgba(37,99,235,.4)}82%{box-shadow:0 12px 28px rgba(30,64,175,.28),0 0 0 10px rgba(37,99,235,0)}}
@keyframes velocity-chat-in{from{opacity:0;transform:translateY(12px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes velocity-message-in{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:translateY(0)}}
@keyframes velocity-caret{0%,45%{opacity:1}46%,100%{opacity:0}}
@media(prefers-reduced-motion:reduce){#velocity-chat-widget [data-chat-open],#velocity-chat-panel,#velocity-chat-messages>div,.velocity-assistant-typing:after{animation:none}}
@media(max-width:560px){#velocity-chat-widget{right:18px;bottom:88px}#velocity-chat-widget [data-chat-open]{width:56px;height:56px}}
</style>

<div id="velocity-chat-widget" class="fixed bottom-5 right-5 z-[9999] font-sans text-slate-900">
    <div id="velocity-chat-panel" class="hidden mb-3 w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
        <div class="bg-gradient-to-br from-slate-950 via-blue-950 to-blue-800 px-5 py-4 text-white">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-200">Velocity System AI</p>
                    <h2 class="mt-1 text-lg font-bold">Ada yang bisa kami bantu?</h2>
                    <p class="mt-1 text-xs text-blue-100">Pilih FAQ atau kirim pertanyaan kepada kami.</p>
                </div>
                <button type="button" data-chat-close class="rounded-lg p-2 text-blue-100 transition hover:bg-white/10 hover:text-white" aria-label="Tutup widget">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>

        <div class="border-b border-slate-200 bg-slate-50 px-4 pt-3">
            <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-200/70 p-1" role="tablist" aria-label="Pilihan bantuan">
                <button type="button" data-chat-tab="chat" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-blue-800 shadow-sm" role="tab" aria-selected="true">Live Chat</button>
                <button type="button" data-chat-tab="faq" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-500 transition hover:text-blue-800" role="tab" aria-selected="false">FAQ</button>
            </div>
        </div>

        <div data-chat-content="chat" class="p-5" role="tabpanel">
            <div id="velocity-chat-messages" class="mb-3 max-h-56 space-y-2 overflow-y-auto rounded-xl bg-slate-50 p-3" aria-live="polite">
                <p data-chat-empty class="py-5 text-center text-sm leading-6 text-slate-500">Belum ada pesan. Silakan tulis pertanyaan Anda di bawah.</p>
            </div>
            <form id="velocity-chat-form" class="flex items-end gap-2">
                <label class="sr-only" for="velocity-chat-input">Pesan untuk admin</label>
                <textarea id="velocity-chat-input" name="message" rows="2" maxlength="2000" required placeholder="Tulis pesan untuk admin..." class="min-w-0 flex-1 resize-none rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100"></textarea>
                <button type="submit" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-700 text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60" aria-label="Kirim pesan">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z" stroke-linejoin="round"/><path d="M22 2 11 13" stroke-linecap="round"/></svg>
                </button>
            </form>
            <p id="velocity-chat-status" class="mt-2 text-xs text-slate-500" aria-live="polite"></p>
        </div>

        <div data-chat-content="faq" class="hidden max-h-80 overflow-y-auto p-5" role="tabpanel">
            <div class="space-y-2">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="rounded-xl border border-slate-200 bg-white">
                        <button type="button" data-faq-trigger="<?= (int) $index ?>" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-slate-800 transition hover:bg-blue-50" aria-expanded="false" aria-controls="velocity-faq-<?= (int) $index ?>">
                            <span><?= htmlspecialchars($faq['question'], ENT_QUOTES, 'UTF-8') ?></span>
                            <svg class="h-4 w-4 shrink-0 text-blue-700 transition-transform" data-faq-icon="<?= (int) $index ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div id="velocity-faq-<?= (int) $index ?>" data-faq-answer="<?= (int) $index ?>" class="hidden border-t border-slate-100 px-4 py-3 text-sm leading-6 text-slate-600">
                            <?= htmlspecialchars($faq['answer'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <button type="button" data-chat-open class="group flex h-14 w-14 items-center justify-center rounded-full bg-blue-700 text-white shadow-xl shadow-blue-900/25 transition hover:scale-105 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200" aria-label="Buka Tanya AI dan FAQ" aria-expanded="false">
        <svg class="h-7 w-7 transition group-hover:rotate-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 10h8M8 14h5" stroke-linecap="round"/><path d="M20 11.5a7.5 7.5 0 0 1-11.6 6.3L4 19l1.2-3.2A7.5 7.5 0 1 1 20 11.5Z" stroke-linejoin="round"/></svg>
    </button>
</div>

<script>
(function () {
    const widget = document.getElementById('velocity-chat-widget');
    if (!widget) return;

    const panel = widget.querySelector('#velocity-chat-panel');
    const openButton = widget.querySelector('[data-chat-open]');
    const closeButton = widget.querySelector('[data-chat-close]');
    const tabButtons = widget.querySelectorAll('[data-chat-tab]');
    const tabContents = widget.querySelectorAll('[data-chat-content]');
    const chatForm = widget.querySelector('#velocity-chat-form');
    const chatInput = widget.querySelector('#velocity-chat-input');
    const chatMessages = widget.querySelector('#velocity-chat-messages');
    const chatStatus = widget.querySelector('#velocity-chat-status');
    const chatEndpoint = <?= json_encode($chatEndpoint, JSON_UNESCAPED_SLASHES) ?>;
    let lastMessageId = 0;
    let messagesLoading = false;
    let typingInProgress = false;

    function wait(milliseconds) {
        return new Promise(function (resolve) {
            window.setTimeout(resolve, milliseconds);
        });
    }

    async function typeAssistantMessage(element, message) {
        typingInProgress = true;
        element.textContent = '';
        for (let index = 0; index < message.length; index += 1) {
            element.textContent += message[index];
            chatMessages.scrollTop = chatMessages.scrollHeight;
            await wait(message[index] === '\n' ? 120 : 18);
        }
        typingInProgress = false;
    }

    async function renderMessages(messages) {
        const empty = chatMessages.querySelector('[data-chat-empty]');
        if (messages.length > 0 && empty) empty.remove();
        for (const item of messages) {
            lastMessageId = Math.max(lastMessageId, Number(item.id));
            const bubble = document.createElement('div');
            bubble.className = 'flex ' + (item.sender === 'visitor' ? 'justify-end' : 'justify-start');
            const group = document.createElement('div');
            group.className = 'max-w-[85%]';
            if (item.sender !== 'visitor') {
                const sender = document.createElement('p');
                sender.className = 'mb-1 px-1 text-[11px] font-semibold text-blue-700';
                sender.textContent = item.sender === 'assistant' ? 'Velocity Assistant' : 'Admin';
                group.appendChild(sender);
            }
            const text = document.createElement('p');
            text.className = 'rounded-2xl px-3 py-2 text-sm leading-5 ' + (item.sender === 'visitor' ? 'rounded-br-md bg-blue-700 text-white' : 'rounded-bl-md bg-white text-slate-700 shadow-sm');
            if (item.sender === 'assistant') {
                text.classList.add('velocity-assistant-typing');
            } else {
                text.textContent = item.message;
            }
            group.appendChild(text);
            bubble.appendChild(group);
            chatMessages.appendChild(bubble);
            if (item.sender === 'assistant') await typeAssistantMessage(text, item.message);
        }
        if (messages.length > 0) chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    async function loadMessages() {
        if (messagesLoading || typingInProgress) return;
        messagesLoading = true;
        try {
            const response = await fetch(chatEndpoint + '?after=' + lastMessageId, {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (data.success) await renderMessages(data.messages || []);
        } catch (error) {
            chatStatus.textContent = 'Chat belum dapat terhubung. Coba lagi sebentar.';
        } finally {
            messagesLoading = false;
        }
    }

    function setPanelVisibility(isVisible) {
        panel.classList.toggle('hidden', !isVisible);
        openButton.setAttribute('aria-expanded', String(isVisible));
        if (isVisible) closeButton.focus();
    }

    function activateTab(tabName) {
        tabButtons.forEach(function (button) {
            const isActive = button.dataset.chatTab === tabName;
            button.classList.toggle('bg-white', isActive);
            button.classList.toggle('text-blue-800', isActive);
            button.classList.toggle('text-slate-500', !isActive);
            button.classList.toggle('shadow-sm', isActive);
            button.setAttribute('aria-selected', String(isActive));
        });
        tabContents.forEach(function (content) {
            content.classList.toggle('hidden', content.dataset.chatContent !== tabName);
        });
    }

    openButton.addEventListener('click', function () {
        const shouldOpen = panel.classList.contains('hidden');
        setPanelVisibility(shouldOpen);
        if (shouldOpen) loadMessages();
    });
    closeButton.addEventListener('click', function () {
        setPanelVisibility(false);
        openButton.focus();
    });
    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activateTab(button.dataset.chatTab);
        });
    });
    chatForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;
        const submitButton = chatForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        chatStatus.textContent = 'Mengirim pesan...';
        try {
            const response = await fetch(chatEndpoint, {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json'}, body: new URLSearchParams({message: message})});
            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Pesan gagal dikirim.');
            chatInput.value = '';
            chatStatus.textContent = 'Pesan terkirim. Admin akan membalas di sini.';
            await loadMessages();
        } catch (error) {
            chatStatus.textContent = error.message || 'Pesan gagal dikirim.';
        } finally {
            submitButton.disabled = false;
        }
    });
    widget.querySelectorAll('[data-faq-trigger]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            const index = trigger.dataset.faqTrigger;
            const answer = widget.querySelector('[data-faq-answer="' + index + '"]');
            const icon = widget.querySelector('[data-faq-icon="' + index + '"]');
            const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
            trigger.setAttribute('aria-expanded', String(!isExpanded));
            answer.classList.toggle('hidden', isExpanded);
            icon.classList.toggle('rotate-180', !isExpanded);
        });
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !panel.classList.contains('hidden')) {
            setPanelVisibility(false);
            openButton.focus();
        }
    });
    loadMessages();
    window.setInterval(loadMessages, 10000);
}());
</script>
