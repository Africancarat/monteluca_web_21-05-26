{{-- Luca AI Chat Widget --}}
<input type="file" id="luca-image-input" accept="image/*" style="display:none">

<div id="luca-chat-widget">

    {{-- Floating toggle button --}}
    <button id="luca-chat-toggle" aria-label="Chat with Luca" title="Chat with Luca">
        <svg id="luca-icon-chat" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <svg id="luca-icon-close" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
             style="display:none">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    {{-- Chat window --}}
    <div id="luca-chat-window" role="dialog" aria-label="Luca AI Chat" style="display:none">

        {{-- Header --}}
        <div class="luca-header">
            <div class="luca-header-avatar">L</div>
            <div class="luca-header-info">
                <span class="luca-header-name">Luca</span>
                <span class="luca-header-sub">Monte Luca AI &bull; Online</span>
            </div>
            <button class="luca-header-close" id="luca-close-btn" aria-label="Close chat">&times;</button>
        </div>

        {{-- Messages --}}
        <div id="luca-messages" class="luca-messages"></div>

        {{-- Product cards --}}
        <div id="luca-products" class="luca-products" style="display:none"></div>

        {{-- Image preview bar (shown when image is queued) --}}
        <div id="luca-image-preview-bar" style="display:none">
            <img id="luca-image-preview-thumb" src="" alt="Selected image">
            <span id="luca-image-preview-label">Image ready to send</span>
            <button id="luca-image-preview-clear" aria-label="Remove image">&times;</button>
        </div>

        {{-- Input --}}
        <div class="luca-input-area">
            <button id="luca-image-btn" aria-label="Search by image" title="Search by image">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
            </button>
            <input id="luca-input" type="text" placeholder="Ask about jewellery, orders…" autocomplete="off"
                   maxlength="400" />
            <button id="luca-send-btn" aria-label="Send">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
    #luca-chat-widget {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        font-family: 'Jost', 'Arial', sans-serif;
    }

    /* ── Toggle button ── */
    #luca-chat-toggle {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        border: none;
        background: #b8973b;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(184,151,59,0.45);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        outline: none;
    }
    #luca-chat-toggle:hover {
        transform: scale(1.07);
        box-shadow: 0 6px 26px rgba(184,151,59,0.55);
    }

    /* ── Chat window ── */
    #luca-chat-window {
        position: absolute;
        bottom: 72px;
        right: 0;
        width: 360px;
        max-height: 580px;
        background: #0f0f0f;
        border: 1px solid #2a2a2a;
        border-radius: 16px;
        box-shadow: 0 12px 48px rgba(0,0,0,0.55);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: lucaSlideUp 0.22s ease;
    }
    @keyframes lucaSlideUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Header ── */
    .luca-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        background: #161616;
        border-bottom: 1px solid #222;
        flex-shrink: 0;
    }
    .luca-header-avatar {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: #b8973b;
        color: #0f0f0f;
        font-weight: 700; font-size: 15px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .luca-header-info { display: flex; flex-direction: column; flex: 1; }
    .luca-header-name { color: #f0e6c8; font-size: 14px; font-weight: 600; letter-spacing: 0.03em; }
    .luca-header-sub  { color: #7a7a7a; font-size: 11px; }
    .luca-header-close {
        background: none; border: none; color: #666;
        font-size: 22px; cursor: pointer; line-height: 1; padding: 0 2px;
        transition: color 0.15s;
    }
    .luca-header-close:hover { color: #ccc; }

    /* ── Messages ── */
    .luca-messages {
        flex: 1;
        overflow-y: auto;
        padding: 14px 14px 6px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        scrollbar-width: thin;
        scrollbar-color: #2a2a2a #0f0f0f;
    }
    .luca-messages::-webkit-scrollbar { width: 4px; }
    .luca-messages::-webkit-scrollbar-track { background: #0f0f0f; }
    .luca-messages::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 4px; }

    /* ── Bubbles ── */
    .luca-bubble {
        max-width: 86%;
        padding: 9px 13px;
        border-radius: 12px;
        font-size: 13.5px;
        line-height: 1.5;
        word-break: break-word;
    }
    .luca-bubble-bot     { background: #1c1c1c; color: #e0d4b8; border-bottom-left-radius: 4px; align-self: flex-start; }
    .luca-bubble-user    { background: #b8973b; color: #0f0f0f; border-bottom-right-radius: 4px; align-self: flex-end; font-weight: 500; }
    .luca-bubble-typing  { background: #1c1c1c; color: #666; align-self: flex-start; font-style: italic; font-size: 12.5px; }

    /* ── Image bubble (user sent image) ── */
    .luca-bubble-img {
        align-self: flex-end;
        border-radius: 12px;
        border-bottom-right-radius: 4px;
        overflow: hidden;
        max-width: 160px;
    }
    .luca-bubble-img img {
        width: 160px;
        height: 120px;
        object-fit: cover;
        display: block;
    }
    .luca-bubble-img-label {
        background: #b8973b;
        color: #0f0f0f;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px;
        text-align: center;
        letter-spacing: 0.04em;
    }

    /* ── Product cards ── */
    .luca-products {
        padding: 6px 14px 10px;
        display: flex;
        gap: 8px;
        overflow-x: auto;
        flex-shrink: 0;
        scrollbar-width: thin;
        scrollbar-color: #2a2a2a #0f0f0f;
    }
    .luca-products::-webkit-scrollbar { height: 3px; }
    .luca-products::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 4px; }
    .luca-product-card {
        flex-shrink: 0;
        width: 130px;
        background: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 10px;
        overflow: hidden;
        text-decoration: none;
        transition: border-color 0.2s;
        display: flex;
        flex-direction: column;
    }
    .luca-product-card:hover { border-color: #b8973b; }
    .luca-product-card img {
        width: 100%; height: 100px;
        object-fit: cover; display: block; background: #222;
    }
    .luca-product-card-no-img {
        width: 100%; height: 100px;
        background: #222;
        display: flex; align-items: center; justify-content: center;
        color: #444; font-size: 28px;
    }
    .luca-product-card-body { padding: 7px 8px 9px; flex: 1; display: flex; flex-direction: column; gap: 3px; }
    .luca-product-card-name {
        color: #d4c4a0; font-size: 11.5px; font-weight: 500; line-height: 1.35;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .luca-product-card-price { color: #b8973b; font-size: 12px; font-weight: 600; }

    /* ── Image preview bar ── */
    #luca-image-preview-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        background: #161616;
        border-top: 1px solid #252525;
        flex-shrink: 0;
    }
    #luca-image-preview-thumb {
        width: 36px; height: 36px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #b8973b;
        flex-shrink: 0;
    }
    #luca-image-preview-label {
        flex: 1;
        color: #b8973b;
        font-size: 12px;
        font-style: italic;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #luca-image-preview-clear {
        background: none; border: none; color: #666;
        font-size: 18px; cursor: pointer; padding: 0;
        transition: color 0.15s; flex-shrink: 0;
    }
    #luca-image-preview-clear:hover { color: #ccc; }

    /* ── Input area ── */
    .luca-input-area {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 12px 12px;
        border-top: 1px solid #1e1e1e;
        flex-shrink: 0;
        background: #0f0f0f;
    }
    #luca-image-btn {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: #1c1c1c;
        border: 1px solid #2a2a2a;
        color: #888;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: border-color 0.15s, color 0.15s;
    }
    #luca-image-btn:hover        { border-color: #b8973b; color: #b8973b; }
    #luca-image-btn.has-image    { border-color: #b8973b; color: #b8973b; background: #1e1a10; }
    #luca-input {
        flex: 1;
        background: #1c1c1c;
        border: 1px solid #2a2a2a;
        border-radius: 20px;
        color: #e0d4b8;
        font-size: 13px;
        padding: 9px 14px;
        outline: none;
        transition: border-color 0.15s;
    }
    #luca-input::placeholder { color: #555; }
    #luca-input:focus { border-color: #b8973b; }
    #luca-send-btn {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: #b8973b;
        border: none; color: #0f0f0f;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: background 0.15s;
    }
    #luca-send-btn:hover    { background: #cda940; }
    #luca-send-btn:disabled { opacity: 0.5; cursor: default; }

    @media (max-width: 480px) {
        #luca-chat-window {
            width: calc(100vw - 32px);
            right: 0; bottom: 70px;
            max-height: 72vh;
        }
    }
</style>

<script>
    (function () {
        const CHAT_URL   = '{{ rtrim(config("chatbot.node_url"), "/") }}/api/chat';
        const USER_EMAIL = '{{ Auth::check() ? Auth::user()->email : "" }}';

        let history      = [];
        let isOpen       = false;
        let isBusy       = false;
        let welcomed     = false;
        let pendingImage = null; // { data: base64string, mediaType: string, previewUrl: string }

        const toggle      = document.getElementById('luca-chat-toggle');
        const iconChat    = document.getElementById('luca-icon-chat');
        const iconClose   = document.getElementById('luca-icon-close');
        const chatWin     = document.getElementById('luca-chat-window');
        const closeBtn    = document.getElementById('luca-close-btn');
        const messagesEl  = document.getElementById('luca-messages');
        const productsEl  = document.getElementById('luca-products');
        const inputEl     = document.getElementById('luca-input');
        const sendBtn     = document.getElementById('luca-send-btn');
        const imageBtn    = document.getElementById('luca-image-btn');
        const fileInput   = document.getElementById('luca-image-input');
        const previewBar  = document.getElementById('luca-image-preview-bar');
        const previewThumb= document.getElementById('luca-image-preview-thumb');
        const previewLabel= document.getElementById('luca-image-preview-label');
        const previewClear= document.getElementById('luca-image-preview-clear');

        /* ── Open / close ── */
        function openChat() {
            isOpen = true;
            chatWin.style.display = 'flex';
            chatWin.style.flexDirection = 'column';
            iconChat.style.display  = 'none';
            iconClose.style.display = 'block';
            if (!welcomed) {
                welcomed = true;
                appendBot("Hello! I'm Luca, your Monte Luca personal jewellery consultant. How can I help you today? 💍\n\nYou can also tap the 🖼 image button to search by photo!");
            }
            setTimeout(() => inputEl.focus(), 100);
        }
        function closeChat() {
            isOpen = false;
            chatWin.style.display = 'none';
            iconChat.style.display  = 'block';
            iconClose.style.display = 'none';
        }
        toggle.addEventListener('click', () => isOpen ? closeChat() : openChat());
        closeBtn.addEventListener('click', closeChat);
        inputEl.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
        sendBtn.addEventListener('click', sendMessage);

        /* ── Image upload ── */
        imageBtn.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', async () => {
            const file = fileInput.files[0];
            if (!file) return;
            fileInput.value = ''; // reset so same file can be re-selected

            try {
                const compressed = await compressImage(file, 800, 0.82);
                pendingImage = compressed;
                previewThumb.src       = compressed.previewUrl;
                previewLabel.textContent = file.name.length > 28 ? file.name.slice(0, 25) + '…' : file.name;
                previewBar.style.display = 'flex';
                imageBtn.classList.add('has-image');
                inputEl.placeholder = 'Add a message or send image…';
                inputEl.focus();
            } catch (e) {
                appendBot("Sorry, I couldn't load that image. Please try a JPG or PNG.");
            }
        });

        previewClear.addEventListener('click', clearImage);

        function clearImage() {
            pendingImage = null;
            previewBar.style.display = 'none';
            previewThumb.src = '';
            imageBtn.classList.remove('has-image');
            inputEl.placeholder = 'Ask about jewellery, orders…';
        }

        /* ── Compress image with canvas ── */
        function compressImage(file, maxPx, quality) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onerror = reject;
                reader.onload = (e) => {
                    const img = new Image();
                    img.onerror = reject;
                    img.onload = () => {
                        const scale = Math.min(1, maxPx / Math.max(img.width, img.height));
                        const w = Math.round(img.width  * scale);
                        const h = Math.round(img.height * scale);
                        const canvas = document.createElement('canvas');
                        canvas.width  = w;
                        canvas.height = h;
                        canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                        const dataUrl   = canvas.toDataURL('image/jpeg', quality);
                        const base64    = dataUrl.split(',')[1];
                        resolve({ data: base64, mediaType: 'image/jpeg', previewUrl: dataUrl });
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        /* ── Append helpers ── */
        function appendBot(text) {
            const div = document.createElement('div');
            div.className   = 'luca-bubble luca-bubble-bot';
            div.textContent = text;
            messagesEl.appendChild(div);
            scrollMessages();
            return div;
        }
        function appendUser(text) {
            const div = document.createElement('div');
            div.className   = 'luca-bubble luca-bubble-user';
            div.textContent = text;
            messagesEl.appendChild(div);
            scrollMessages();
        }
        function appendImageBubble(previewUrl) {
            const wrap = document.createElement('div');
            wrap.className = 'luca-bubble-img';
            const img = document.createElement('img');
            img.src = previewUrl; img.alt = 'Your image';
            const label = document.createElement('div');
            label.className   = 'luca-bubble-img-label';
            label.textContent = '🔍 Image search';
            wrap.appendChild(img);
            wrap.appendChild(label);
            messagesEl.appendChild(wrap);
            scrollMessages();
        }
        function showTyping() {
            const div = document.createElement('div');
            div.className   = 'luca-bubble luca-bubble-typing';
            div.id          = 'luca-typing';
            div.textContent = 'Luca is typing…';
            messagesEl.appendChild(div);
            scrollMessages();
        }
        function removeTyping() {
            const t = document.getElementById('luca-typing');
            if (t) t.remove();
        }

        /* ── Product cards ── */
        function renderProducts(products) {
            productsEl.innerHTML = '';
            if (!products || !products.length) { productsEl.style.display = 'none'; return; }
            products.forEach(p => {
                const a = document.createElement('a');
                a.className = 'luca-product-card';
                a.href      = p.url || '#';
                a.target    = '_blank';
                a.rel       = 'noopener';

                if (p.image) {
                    const img = document.createElement('img');
                    img.src     = p.image;
                    img.alt     = p.name;
                    img.loading = 'lazy';
                    img.onerror = () => { img.replaceWith(noImgPlaceholder()); };
                    a.appendChild(img);
                } else {
                    a.appendChild(noImgPlaceholder());
                }

                const body  = document.createElement('div');
                body.className = 'luca-product-card-body';
                const name  = document.createElement('div');
                name.className   = 'luca-product-card-name';
                name.textContent = p.name;
                const price = document.createElement('div');
                price.className   = 'luca-product-card-price';
                price.textContent = p.price || '';
                body.appendChild(name);
                body.appendChild(price);
                a.appendChild(body);
                productsEl.appendChild(a);
            });
            productsEl.style.display = 'flex';
            scrollMessages();
        }

        function noImgPlaceholder() {
            const ph = document.createElement('div');
            ph.className = 'luca-product-card-no-img';
            ph.innerHTML = '&#128141;';
            return ph;
        }

        function scrollMessages() { messagesEl.scrollTop = messagesEl.scrollHeight; }

        /* ── Send message ── */
        async function sendMessage() {
            const text  = inputEl.value.trim();
            const image = pendingImage;

            if (!text && !image) return;
            if (isBusy) return;

            isBusy = true;
            sendBtn.disabled = true;
            inputEl.value = '';
            productsEl.style.display = 'none';
            productsEl.innerHTML = '';

            // Show what the user sent
            if (image) {
                appendImageBubble(image.previewUrl);
                if (text) appendUser(text);
                clearImage();
            } else {
                appendUser(text);
            }

            showTyping();

            const displayText = text || 'Find similar jewellery to this image';

            try {
                const body = {
                    message: displayText,
                    email:   USER_EMAIL,
                    history: history,
                };
                if (image) {
                    body.image = { data: image.data, mediaType: image.mediaType };
                }

                const res  = await fetch(CHAT_URL, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify(body),
                });

                const data  = await res.json();
                removeTyping();

                const reply = data.reply || "I'm having trouble right now — please try again.";
                appendBot(reply);
                renderProducts(data.products || []);

                history.push({ role: 'user',      content: displayText });
                history.push({ role: 'assistant',  content: reply });
                if (history.length > 20) history = history.slice(-20);

            } catch (err) {
                removeTyping();
                appendBot("Sorry, I couldn't reach the server. Please try again in a moment.");
            }

            isBusy = false;
            sendBtn.disabled = false;
            inputEl.focus();
        }
    })();
</script>