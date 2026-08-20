{{-- Floating BangBang chat widget (bottom-right).
     Landing card matches BangBang branding, then routes into
     Real-Time Chat Support (automated) or Chat Support (signed-in rooms/contacts). --}}
<style>
#chatWidget {
    --bb-primary: #22c55e;
    --bb-primary-dark: #16a34a;
    --bb-primary-soft: #dcfce7;
    --bb-bg: #ffffff;
    --bb-panel-bg: #eaf6ea;
    --bb-fg: #111827;
    --bb-muted: #6b7280;
    --bb-border: #e5e7eb;
    --bb-bubble-mine: #dcfce7;
    --bb-bubble-them: #f3f4f6;
    --bb-shadow: 0 12px 32px rgba(0,0,0,0.14);
    position: fixed; right: 20px; bottom: 20px; z-index: 1100; font-family: inherit;
}
#chatWidgetToggle {
    width: 56px; height: 56px; border-radius: 50%;
    background: var(--bb-primary); color: #fff; border: 0;
    box-shadow: var(--bb-shadow); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s, transform .1s; position: relative;
}
#chatWidgetToggle:hover { background: var(--bb-primary-dark); }
#chatWidgetToggle:active { transform: scale(0.96); }
#chatWidgetToggle svg { width: 26px; height: 26px; }
#chatWidgetToggle .cw-badge {
    position: absolute; top: 2px; right: 2px; min-width: 18px; height: 18px;
    padding: 0 5px; border-radius: 9px; background: #ef4444; color: #fff;
    font-size: 11px; font-weight: 600; display: none;
    align-items: center; justify-content: center;
}
#chatWidgetPanel {
    position: absolute; right: 0; bottom: 72px;
    width: 380px; max-width: calc(100vw - 24px);
    height: 620px; max-height: calc(100vh - 100px);
    background: var(--bb-panel-bg); color: var(--bb-fg);
    border-radius: 18px; box-shadow: var(--bb-shadow);
    display: none; flex-direction: column; overflow: hidden;
}
#chatWidgetPanel.open { display: flex; }
#chatWidget .cw-panel-header {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 12px; background: var(--bb-panel-bg);
    border-bottom: 1px solid transparent;
}
#chatWidget .cw-panel-header.with-room {
    background: #fff; border-bottom-color: #f3f4f6;
}
#chatWidget .cw-back, #chatWidget .cw-close {
    background: transparent; border: 0; color: var(--bb-fg); cursor: pointer;
    padding: 4px 8px; border-radius: 8px; font-size: 16px; flex-shrink: 0;
}
#chatWidget .cw-back:hover, #chatWidget .cw-close:hover { background: rgba(0,0,0,0.05); }
#chatWidget .cw-back { visibility: hidden; }
#chatWidget .cw-back.show { visibility: visible; }
/* Exit icon only appears when we're inside a room; hidden on landing/list. */
#chatWidget .cw-close { display: none; }
#chatWidget .cw-panel-header.with-room .cw-close { display: inline-flex; align-items: center; }
#chatWidget .cw-header-info {
    flex: 1; display: none; align-items: center; gap: 10px; min-width: 0;
}
#chatWidget .cw-panel-header.with-room .cw-header-info { display: flex; }
#chatWidget .cw-header-info .cw-avatar { width: 32px; height: 32px; }
#chatWidget .cw-header-info .cw-avatar .cw-presence {
    position: absolute; right: -1px; bottom: -1px; width: 9px; height: 9px;
}
#chatWidget .cw-header-info-text { flex: 1; min-width: 0; }
#chatWidget .cw-header-title {
    font-weight: 700; font-size: 14px; color: #111827;
    display: flex; align-items: center; gap: 6px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
#chatWidget .cw-header-title-dot {
    width: 8px; height: 8px; border-radius: 50%; background: var(--bb-primary);
    flex-shrink: 0;
}
#chatWidget .cw-header-sub { font-size: 11px; color: var(--bb-muted); margin-top: 1px; }

/* Landing card */
#chatWidget .cw-landing {
    background: #fff; border-radius: 18px; margin: 12px 14px;
    padding: 22px 22px 16px; box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    flex: 1; display: flex; flex-direction: column;
}
#chatWidget .cw-welcome-head { padding: 8px 4px 16px; text-align: center; }
#chatWidget .cw-brand { display: flex; justify-content: center; margin-bottom: 14px; }
#chatWidget .cw-brand-logo {
    width: 60px; height: 60px; border-radius: 50%;
    background: var(--bb-primary); color: #fff;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(34,197,94,0.25);
}
#chatWidget .cw-brand-logo svg { width: 28px; height: 28px; }
#chatWidget .cw-h1 {
    font-size: 22px; font-weight: 800; color: #111827; margin: 10px 0 8px; line-height: 1.25;
}
#chatWidget .cw-welcome-sub { font-size: 14px; color: #6b7280; line-height: 1.5; }
#chatWidget .cw-start-card {
    margin-top: auto; padding: 14px 16px;
    background: #fff; border: 1px solid var(--bb-border); border-radius: 14px;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    cursor: pointer;
}
#chatWidget .cw-start-card:hover { border-color: #d1d5db; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
#chatWidget .cw-start-status {
    display: flex; align-items: center; gap: 8px;
    font-weight: 700; font-size: 14px; color: #111827;
}
#chatWidget .cw-online-dot {
    width: 8px; height: 8px; border-radius: 50%; background: var(--bb-primary);
    box-shadow: 0 0 0 3px rgba(34,197,94,0.15);
}
#chatWidget .cw-start-eta { font-size: 12px; color: #6b7280; margin-top: 4px; }
#chatWidget .cw-start-btn {
    margin-top: 10px; padding: 14px 16px;
    background: #fff; border: 1px solid var(--bb-border); border-radius: 14px;
    display: flex; align-items: center; justify-content: space-between;
    font-weight: 700; font-size: 14px; color: #111827; cursor: pointer;
    text-align: left; width: 100%;
}
#chatWidget .cw-start-btn:hover { border-color: #d1d5db; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
#chatWidget .cw-start-arrow { color: #9ca3af; font-size: 18px; }

/* List view (rooms/contacts) */
#chatWidget .cw-view-inner { flex: 1; display: flex; flex-direction: column; background: #fff; border-radius: 18px 18px 0 0; margin-top: 0; overflow: hidden; }
#chatWidget .cw-tabs {
    display: flex; border-bottom: 1px solid var(--bb-border);
}
#chatWidget .cw-tab {
    flex: 1; padding: 12px; text-align: center; font-size: 13px; font-weight: 700;
    color: var(--bb-muted); cursor: pointer; border: 0; background: transparent;
    border-bottom: 2px solid transparent;
}
#chatWidget .cw-tab.active { color: var(--bb-primary); border-bottom-color: var(--bb-primary); }
#chatWidget .cw-search { display: flex; padding: 8px 10px; border-bottom: 1px solid var(--bb-border); }
#chatWidget .cw-search input {
    flex: 1; padding: 7px 12px; border: 1px solid var(--bb-border);
    border-radius: 999px; font: inherit; font-size: 13px; outline: none;
}
#chatWidget .cw-search input:focus { border-color: var(--bb-primary); }
#chatWidget .cw-body { flex: 1 1 auto; overflow-y: auto; padding: 4px 0; background: #fff; }
#chatWidget .cw-list { display: flex; flex-direction: column; }
#chatWidget .cw-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; cursor: pointer;
}
#chatWidget .cw-item:hover { background: #f9fafb; }
#chatWidget .cw-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--bb-primary-soft); color: var(--bb-primary-dark);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 14px; flex-shrink: 0; position: relative;
    background-size: cover; background-position: center;
}
#chatWidget .cw-avatar .cw-presence {
    position: absolute; right: -2px; bottom: -2px; width: 11px; height: 11px;
    border-radius: 50%; background: #d1d5db; border: 2px solid #fff;
}
#chatWidget .cw-avatar .cw-presence.online { background: var(--bb-primary); }
#chatWidget .cw-item .cw-meta { flex: 1; min-width: 0; }
#chatWidget .cw-item .cw-name { font-weight: 600; font-size: 14px; color: var(--bb-fg); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
#chatWidget .cw-item .cw-msub { font-size: 12px; color: var(--bb-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
#chatWidget .cw-item .cw-time { font-size: 10px; color: var(--bb-muted); flex-shrink: 0; }
#chatWidget .cw-item .cw-unread {
    background: var(--bb-primary); color: #fff; border-radius: 999px;
    font-size: 10px; font-weight: 700; padding: 2px 7px; min-width: 18px; text-align: center;
}

/* Room view */
#chatWidget .cw-room-view { flex: 1; display: flex; flex-direction: column; background: #fff; overflow: hidden; }
/* Bot "typing…" bubble with animated dots */
#chatWidget .cw-typing-bubble {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 12px 14px; min-height: 20px;
}
#chatWidget .cw-typing-dot {
    width: 6px; height: 6px; border-radius: 50%; background: #9ca3af;
    animation: cw-td 1.2s infinite ease-in-out;
}
#chatWidget .cw-typing-dot:nth-child(2) { animation-delay: 0.15s; }
#chatWidget .cw-typing-dot:nth-child(3) { animation-delay: 0.3s; }
@keyframes cw-td {
    0%, 60%, 100% { opacity: 0.25; transform: translateY(0); }
    30% { opacity: 1; transform: translateY(-3px); }
}
#chatWidget .cw-feedback-view { flex: 1; display: flex; flex-direction: column; background: #fff; overflow: hidden; }
#chatWidget .cw-feedback-body { flex: 1; padding: 20px 24px; text-align: center; overflow-y: auto; }
#chatWidget .cw-feedback-title { font-size: 15px; font-weight: 600; color: #111827; margin-bottom: 14px; }
#chatWidget .cw-feedback-copy { font-size: 13px; color: #6b7280; line-height: 1.6; margin-bottom: 12px; }
#chatWidget .cw-feedback-emojis {
    display: flex; justify-content: center; gap: 4px; margin: 18px 0 6px;
}
#chatWidget .cw-fb-emoji {
    background: transparent; border: 0; cursor: pointer; padding: 6px;
    font-size: 26px; line-height: 1; border-radius: 50%;
    transition: transform .12s, background .12s; filter: grayscale(1);
    opacity: 0.7;
}
#chatWidget .cw-fb-emoji:hover { transform: scale(1.15); opacity: 1; filter: none; }
#chatWidget .cw-fb-emoji.selected { filter: none; opacity: 1; background: #f3f4f6; transform: scale(1.2); }
#chatWidget .cw-messages {
    display: flex; flex-direction: column; gap: 4px;
    padding: 14px 12px 8px; flex: 1; overflow-y: auto;
    background: #fff;
}
#chatWidget .cw-day-sep {
    display: flex; align-items: center; gap: 10px;
    color: #9ca3af; font-size: 11px; font-weight: 600;
    margin: 6px 4px 12px;
}
#chatWidget .cw-day-sep::before, #chatWidget .cw-day-sep::after {
    content: ''; flex: 1; height: 1px; background: #e5e7eb;
}
#chatWidget .cw-msg-group { display: flex; flex-direction: column; margin-bottom: 8px; }
#chatWidget .cw-msg-group.mine { align-items: flex-end; }
#chatWidget .cw-msg-group.them { align-items: flex-start; }
#chatWidget .cw-msg-row {
    display: flex; align-items: flex-end; gap: 8px; max-width: 82%;
}
#chatWidget .cw-msg-group.mine .cw-msg-row { flex-direction: row-reverse; }
#chatWidget .cw-msg-avatar {
    width: 26px; height: 26px; border-radius: 50%;
    background: #1a1a1a; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
    background-size: cover; background-position: center;
}
#chatWidget .cw-msg-group.mine .cw-msg-avatar { display: none; }
#chatWidget .cw-msg {
    padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.45;
    word-wrap: break-word;
}
#chatWidget .cw-msg-group.mine .cw-msg {
    background: #111827; color: #fff; border-bottom-right-radius: 4px;
}
#chatWidget .cw-msg-group.them .cw-msg {
    background: #fff; color: #111827; border: 1px solid #e5e7eb;
    border-bottom-left-radius: 4px;
}
#chatWidget .cw-msg-name {
    font-size: 11px; color: #6b7280; margin: 4px 6px 0;
}
#chatWidget .cw-msg-group.mine .cw-msg-name { display: none; }
#chatWidget .cw-footer { border-top: 1px solid #f3f4f6; padding: 8px 10px; background: #fff; }
#chatWidget .cw-compose {
    display: flex; align-items: center; gap: 6px;
    background: #fff; border: 1px solid var(--bb-border); border-radius: 22px;
    padding: 4px 8px 4px 14px;
}
#chatWidget .cw-input {
    flex: 1; resize: none; padding: 8px 4px; border: 0;
    font: inherit; font-size: 13px; min-height: 26px; max-height: 96px; outline: none;
    background: transparent;
}
#chatWidget .cw-icon-btn {
    background: transparent; border: 0; color: #6b7280; cursor: pointer;
    width: 32px; height: 32px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center; padding: 0;
}
#chatWidget .cw-icon-btn:hover { background: #f3f4f6; color: #111827; }
#chatWidget .cw-icon-btn svg { width: 18px; height: 18px; }
#chatWidget .cw-send {
    background: var(--bb-primary); color: #fff; border: 0;
    width: 32px; height: 32px; border-radius: 50%; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center; padding: 0;
}
#chatWidget .cw-send:hover { background: var(--bb-primary-dark); }
#chatWidget .cw-send:disabled { opacity: 0.4; cursor: not-allowed; background: #9ca3af; }
#chatWidget .cw-send svg { width: 16px; height: 16px; }
#chatWidget .cw-empty { text-align: center; color: var(--bb-muted); font-size: 12px; padding: 32px 12px; }
#chatWidget .cw-status { font-size: 11px; padding: 6px 12px; }
#chatWidget .cw-status.err { background: #fee2e2; color: #991b1b; }
#chatWidget .cw-status.ok { background: #dcfce7; color: #166534; }
#chatWidget .cw-spinner {
    width: 18px; height: 18px; border: 3px solid #d1d5db; border-top-color: var(--bb-primary);
    border-radius: 50%; animation: cw-spin 0.7s linear infinite; display: inline-block;
}
@keyframes cw-spin { to { transform: rotate(360deg); } }
@media (max-width: 480px) {
    #chatWidgetPanel { width: calc(100vw - 24px); height: calc(100vh - 100px); }
}
</style>

<div id="chatWidget" aria-live="polite">
    <div id="chatWidgetPanel" role="dialog" aria-label="Chat">
        <div class="cw-panel-header" data-cw-panel-header>
            <button type="button" class="cw-back" data-cw-action="back" aria-label="{{ __('messages.chat_back') }}">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </button>
            <div class="cw-header-info" data-cw-header-info>
                <div class="cw-avatar" data-cw-header-avatar>?</div>
                <div class="cw-header-info-text">
                    <div class="cw-header-title">
                        <span data-cw-header-title></span>
                        <span class="cw-header-title-dot"></span>
                    </div>
                    <div class="cw-header-sub" data-cw-header-sub></div>
                </div>
            </div>
            <button type="button" class="cw-close" data-cw-action="end" aria-label="{{ __('messages.chat_end_chat') }}" title="{{ __('messages.chat_end_chat_title') }}">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>
        </div>
        <div class="cw-status" data-cw-status style="display:none;"></div>

        {{-- Landing (BangBang-style header) --}}
        <div class="cw-landing" data-cw-view="landing">
            <div class="cw-welcome-head">
                <div class="cw-brand">
                    <div class="cw-brand-logo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                </div>
                <div class="cw-h1">{{ __('messages.chat_welcome_title', ['app' => config('app.name', 'DRS')]) }}</div>
                <div class="cw-welcome-sub">{{ __('messages.chat_welcome_sub') }}</div>
            </div>
            <div class="cw-start-card" data-cw-choose="start">
                <div>
                    <div class="cw-start-status">
                        <span class="cw-online-dot"></span> {{ __('messages.chat_we_are_online') }}
                    </div>
                    <div class="cw-start-eta">{{ __('messages.chat_reply_eta') }}</div>
                </div>
            </div>
            <button type="button" class="cw-start-btn" data-cw-choose="start">
                {{ __('messages.chat_start_conversation') }}
                <span class="cw-start-arrow">›</span>
            </button>
        </div>

        {{-- Chats/Contacts list --}}
        <div class="cw-view-inner" data-cw-view="list" style="display:none;">
            <div class="cw-tabs">
                <button type="button" class="cw-tab active" data-cw-tab="chats">Chats</button>
                <button type="button" class="cw-tab" data-cw-tab="contacts">Contacts</button>
            </div>
            <div class="cw-search" data-cw-search style="display:none;">
                <input type="search" placeholder="Search contacts…" data-cw-searchinput>
            </div>
            <div class="cw-body" data-cw-list-body>
                <div class="cw-empty"><span class="cw-spinner"></span></div>
            </div>
        </div>

        {{-- Room view --}}
        <div class="cw-room-view" data-cw-view="room" style="display:none;">
            <div class="cw-messages" data-cw-messages></div>
            <div class="cw-footer">
                <div class="cw-compose">
                    <textarea class="cw-input" data-cw-input placeholder="{{ __('messages.chat_type_message') }}" rows="1"></textarea>
                    <button type="button" class="cw-icon-btn" title="{{ __('messages.chat_attach') }}" aria-label="{{ __('messages.chat_attach') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                    </button>
                    <button type="button" class="cw-icon-btn" title="{{ __('messages.chat_emoji') }}" aria-label="{{ __('messages.chat_emoji') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                    </button>
                    <button type="button" class="cw-send" data-cw-action="send" title="{{ __('messages.chat_send') }}" aria-label="{{ __('messages.chat_send') }}" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Feedback survey view --}}
        <div class="cw-feedback-view" data-cw-view="feedback" style="display:none;">
            <div class="cw-feedback-body">
                <div class="cw-feedback-title">{{ __('messages.chat_fb_title') }}</div>
                <div class="cw-feedback-copy">{{ __('messages.chat_fb_copy') }}</div>
                <div class="cw-feedback-copy" style="font-weight:600;color:#111827;">{{ __('messages.chat_fb_appreciate') }}</div>
                <div class="cw-feedback-emojis" data-cw-feedback-emojis>
                    <button type="button" class="cw-fb-emoji" data-cw-rating="1" aria-label="{{ __('messages.chat_fb_rating_1') }}">😞</button>
                    <button type="button" class="cw-fb-emoji" data-cw-rating="2" aria-label="{{ __('messages.chat_fb_rating_2') }}">😐</button>
                    <button type="button" class="cw-fb-emoji" data-cw-rating="3" aria-label="{{ __('messages.chat_fb_rating_3') }}">🙂</button>
                    <button type="button" class="cw-fb-emoji" data-cw-rating="4" aria-label="{{ __('messages.chat_fb_rating_4') }}">😊</button>
                    <button type="button" class="cw-fb-emoji" data-cw-rating="5" aria-label="{{ __('messages.chat_fb_rating_5') }}">😍</button>
                </div>
            </div>
            <div class="cw-footer">
                <div class="cw-compose">
                    <textarea class="cw-input" data-cw-feedback-input placeholder="{{ __('messages.chat_fb_placeholder') }}" rows="1"></textarea>
                    <button type="button" class="cw-send" data-cw-action="submit-feedback" title="{{ __('messages.chat_fb_submit') }}" aria-label="{{ __('messages.chat_fb_submit') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <button type="button" id="chatWidgetToggle" aria-label="{{ __('messages.chat_open_chat') }}" title="{{ __('messages.chat_open_chat') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <span class="cw-badge" data-cw-badge>0</span>
    </button>
</div>

<script>
(function () {
    const widget = document.getElementById('chatWidget');
    if (!widget) return;

    // JS-side translation table, populated by Blade so it follows the user's locale.
    const T = {
        assistant: @json(__('messages.chat_assistant_name')),
        you: @json(__('messages.chat_you')),
        reply_eta: @json(__('messages.chat_reply_eta')),
        direct: @json(__('messages.chat_direct_message')),
        group: @json(__('messages.chat_group_chat')),
        support: @json(__('messages.chat_support')),
        online: @json(__('messages.chat_online')),
        offline: @json(__('messages.chat_offline')),
        no_messages: @json(__('messages.chat_no_messages')),
        session_expired: @json(__('messages.chat_session_expired')),
        backend_not_configured: @json(__('messages.chat_backend_not_configured')),
        cannot_reach: @json(__('messages.chat_cannot_reach')),
        could_not_start: @json(__('messages.chat_could_not_start')),
        connecting: @json(__('messages.chat_connecting')),
        failed_send: @json(__('messages.chat_failed_send')),
        failed_load_messages: @json(__('messages.chat_failed_load_messages')),
        today: @json(__('messages.chat_today')),
        fb_thanks: @json(__('messages.chat_fb_thanks')),
        fb_pick_rating: @json(__('messages.chat_fb_pick_rating')),
    };
    const toggle = widget.querySelector('#chatWidgetToggle');
    const panel = widget.querySelector('#chatWidgetPanel');
    const status = widget.querySelector('[data-cw-status]');
    const badge = widget.querySelector('[data-cw-badge]');
    const backBtn = widget.querySelector('[data-cw-action="back"]');
    const endBtn = widget.querySelector('[data-cw-action="end"]');
    const feedbackInput = widget.querySelector('[data-cw-feedback-input]');
    const feedbackSubmitBtn = widget.querySelector('[data-cw-action="submit-feedback"]');
    const feedbackEmojis = widget.querySelectorAll('[data-cw-rating]');
    const views = {
        landing:  widget.querySelector('[data-cw-view="landing"]'),
        list:     widget.querySelector('[data-cw-view="list"]'),
        room:     widget.querySelector('[data-cw-view="room"]'),
        feedback: widget.querySelector('[data-cw-view="feedback"]'),
    };
    const listBody = widget.querySelector('[data-cw-list-body]');
    const tabs = widget.querySelectorAll('[data-cw-tab]');
    const searchWrap = widget.querySelector('[data-cw-search]');
    const searchInput = widget.querySelector('[data-cw-searchinput]');
    const panelHeader = widget.querySelector('[data-cw-panel-header]');
    const roomTitle = widget.querySelector('[data-cw-header-title]');
    const roomSub = widget.querySelector('[data-cw-header-sub]');
    const roomAvatar = widget.querySelector('[data-cw-header-avatar]');
    const messagesEl = widget.querySelector('[data-cw-messages]');
    const input = widget.querySelector('[data-cw-input]');
    const sendBtn = widget.querySelector('[data-cw-action="send"]');

    const state = { view: 'landing', tab: 'chats', me: null, rooms: [], contacts: [], room: null, lastMsgId: 0, guestUserId: null, pendingSupport: false, initialRendered: false };

    function csrfFromCookie() {
        const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }
    function csrfFromMeta() {
        return document.querySelector('meta[name=csrf-token]')?.content || '';
    }
    async function j(url, opts) {
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfFromMeta(),
            'X-XSRF-TOKEN': csrfFromCookie(),
            ...(opts?.headers || {}),
        };
        const r = await fetch(url, { credentials: 'same-origin', ...opts, headers });
        const text = await r.text();
        let bd; try { bd = text ? JSON.parse(text) : {}; } catch { bd = null; }
        if (!bd || typeof bd !== 'object') bd = { message: r.status === 419 ? T.session_expired : (r.statusText || 'Request failed') };
        if (!r.ok) throw Object.assign(new Error(bd.message || r.statusText), { status: r.status, body: bd });
        return bd;
    }
    function showStatus(msg, kind) {
        status.textContent = msg;
        status.className = 'cw-status' + (kind ? ' ' + kind : '');
        status.style.display = msg ? 'block' : 'none';
    }
    function setBadge(n) {
        badge.textContent = n > 99 ? '99+' : String(n);
        badge.style.display = n > 0 ? 'flex' : 'none';
    }
    function setView(v) {
        state.view = v;
        Object.entries(views).forEach(([k, el]) => { el.style.display = k === v ? 'flex' : 'none'; });
        backBtn.classList.toggle('show', v !== 'landing');
        panelHeader.classList.toggle('with-room', v === 'room');
    }
    function initials(name) {
        return (name || '').trim().split(/\s+/).slice(0, 2).map(s => s[0] || '').join('').toUpperCase() || '?';
    }
    function relTime(iso) {
        if (!iso) return '';
        const d = new Date(iso), diff = (Date.now() - d.getTime()) / 1000;
        if (diff < 60) return 'now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h';
        return d.toLocaleDateString();
    }
    function avatarEl(person, extra = '') {
        const el = document.createElement('div');
        el.className = 'cw-avatar ' + extra;
        if (person.avatar_url) el.style.backgroundImage = 'url(' + person.avatar_url + ')';
        else el.textContent = initials(person.display_name || person.name || '');
        const p = document.createElement('div');
        p.className = 'cw-presence' + (person.is_online ? ' online' : '');
        el.appendChild(p);
        return el;
    }

    async function ensureConfig() {
        try {
            const cfg = await j('{{ route('chat.config') }}');
            if (!cfg.configured) {
                showStatus(cfg.message || T.backend_not_configured, 'err');
                return null;
            }
            showStatus('', '');
            state.me = cfg.user || null;
            state.wsUrl = cfg.ws_url || null;
            state.accessToken = cfg.access_token || null;
            wsConnect();
            return cfg;
        } catch (e) {
            showStatus(e.body?.message || T.cannot_reach, 'err');
            return null;
        }
    }

    // ---------- WebSocket real-time ----------
    let ws = null;
    let wsRetryDelay = 1000;
    let wsHeartbeat = null;
    let typingSentAt = 0;
    const typingUsers = new Map(); // roomId -> Set(userId)
    function wsConnect() {
        if (!state.wsUrl || !state.accessToken) return;
        if (ws && (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING)) return;
        try {
            const sep = state.wsUrl.includes('?') ? '&' : '?';
            ws = new WebSocket(state.wsUrl + sep + 'token=' + encodeURIComponent(state.accessToken));
        } catch (e) { console.warn('[chat] ws construct fail', e); scheduleReconnect(); return; }
        ws.addEventListener('open', () => {
            wsRetryDelay = 1000;
            try { ws.send(JSON.stringify({ type: 'auth', token: state.accessToken })); } catch {}
            if (wsHeartbeat) clearInterval(wsHeartbeat);
            wsHeartbeat = setInterval(() => { try { ws.send(JSON.stringify({ type: 'ping' })); } catch {} }, 25000);
        });
        ws.addEventListener('close', () => {
            if (wsHeartbeat) { clearInterval(wsHeartbeat); wsHeartbeat = null; }
            scheduleReconnect();
        });
        ws.addEventListener('error', () => {});
        ws.addEventListener('message', (evt) => {
            let msg; try { msg = JSON.parse(evt.data); } catch { return; }
            handleWs(msg);
        });
    }
    function scheduleReconnect() {
        if (!panel.classList.contains('open')) return;
        wsRetryDelay = Math.min(wsRetryDelay * 1.7, 20000);
        setTimeout(wsConnect, wsRetryDelay);
    }
    function wsSend(payload) {
        if (ws && ws.readyState === WebSocket.OPEN) {
            try { ws.send(JSON.stringify(payload)); return true; } catch {}
        }
        return false;
    }
    function handleWs(msg) {
        const type = msg.type || msg.event || '';
        const data = msg.data || msg.payload || msg;
        if (!type) return;
        switch (type) {
            case 'pong': case 'ping': return;
            case 'message.new': case 'message.created': case 'message':
                onIncomingMessage(data); return;
            case 'message.updated': case 'message.edited':
                if (state.room && (data.room_id === state.room.id)) refreshMessages(); return;
            case 'message.deleted':
                if (state.room && (data.room_id === state.room.id)) refreshMessages(); return;
            case 'presence.update': case 'presence.updated': case 'user.presence':
                onPresence(data); return;
            case 'typing.start': case 'typing':
                onTyping(data, true); return;
            case 'typing.stop':
                onTyping(data, false); return;
            case 'read.receipt': case 'message.read':
                return; // could show ✓✓ later
            case 'notification': case 'notification.new':
                pollUnread(); return;
            default:
                console.debug('[chat] unknown ws event:', type, data);
        }
    }
    function onIncomingMessage(m) {
        const roomId = m.room_id ?? m.room?.id;
        if (state.room && state.room.id === roomId) {
            // Sender clearly stopped typing since they just sent a message.
            const sid = m.sender_id ?? m.sender?.id;
            if (sid) {
                const set = typingUsers.get(roomId);
                if (set && set.has(sid)) { set.delete(sid); renderTypingHint(set); }
            }
            appendMessage(m);
        } else {
            pollUnread();
            if (state.view === 'list' && state.tab === 'chats') switchTab('chats');
        }
    }
    function onPresence(p) {
        const uid = p.user_id ?? p.id;
        const online = p.status === 'online' || p.is_online === true;
        if (state.tab === 'contacts' && state.view === 'list') {
            const u = state.contacts.find(x => x.id === uid);
            if (u) { u.is_online = online; renderContacts(); }
        }
        if (state.room) {
            const other = (state.room.members || []).find(m => (m.id ?? m.user_id) === uid);
            if (other) { other.is_online = online; roomSub.textContent = state.room.type === 'support' ? T.reply_eta : (state.room.type === 'direct' ? (online ? T.online : T.offline) : T.group); }
        }
    }
    function onTyping(t, isStart) {
        if (!state.room) return;
        const rid = t.room_id ?? t.room?.id;
        if (rid !== state.room.id) return;
        const uid = t.user_id ?? t.user?.id;
        if (!uid || uid === state.me?.id) return;
        let set = typingUsers.get(rid) || new Set();
        if (isStart) set.add(uid); else set.delete(uid);
        typingUsers.set(rid, set);
        renderTypingHint(set);
    }
    function renderTypingHint(set) {
        let el = messagesEl.querySelector('.cw-typing');
        if (!set || set.size === 0) { if (el) el.remove(); return; }
        if (!el) {
            el = document.createElement('div');
            el.className = 'cw-typing cw-empty';
            el.style.padding = '4px 12px'; el.style.textAlign = 'left'; el.style.fontStyle = 'italic';
            messagesEl.appendChild(el);
        }
        el.textContent = 'typing…';
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
    // Simulated bot-typing indicator (BangBang doesn't send WS typing events
    // for the assistant, so we display one client-side right after each send
    // and auto-clear it when the bot's message arrives or ~10 s pass).
    let botTypingTimer = null;
    function showBotTyping() {
        clearTimeout(botTypingTimer);
        // Remove any previous instance to keep it at the bottom.
        hideBotTyping();
        const grp = document.createElement('div');
        grp.className = 'cw-msg-group them cw-bot-typing';
        grp.innerHTML =
            '<div class="cw-msg-row">' +
                '<div class="cw-msg-avatar" style="background:#111827;color:#fff;">BB</div>' +
                '<div class="cw-msg cw-typing-bubble">' +
                    '<span class="cw-typing-dot"></span>' +
                    '<span class="cw-typing-dot"></span>' +
                    '<span class="cw-typing-dot"></span>' +
                '</div>' +
            '</div>' +
            '<div class="cw-msg-name">' + T.assistant + '</div>';
        messagesEl.appendChild(grp);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        botTypingTimer = setTimeout(hideBotTyping, 10000);
    }
    function hideBotTyping() {
        clearTimeout(botTypingTimer); botTypingTimer = null;
        const el = messagesEl.querySelector('.cw-bot-typing');
        if (el) el.remove();
    }
    async function refreshMessages() {
        if (!state.room) return;
        try {
            // Fetch the recent tail and dedup against what's already in the DOM.
            // Simpler + race-free vs after_id (which could skip messages posted
            // between our last render and an optimistic self-append).
            const r = await j('/chat/rooms/' + state.room.id + '/messages?limit=30');
            const msgs = r.messages || [];
            if (!state.initialRendered) {
                renderMessages(msgs);
                state.initialRendered = true;
                return;
            }
            const sorted = msgs.slice().sort((a, b) => {
                if (typeof a.id === 'number' && typeof b.id === 'number') return a.id - b.id;
                return (new Date(a.created_at||0)) - (new Date(b.created_at||0));
            });
            let addedAny = false;
            for (const m of sorted) {
                if (m.id && !alreadyRendered(m.id) && !isHiddenPrimer(m)) {
                    appendMessage(m);
                    addedAny = true;
                }
            }
            if (addedAny) fastPollUntil = Math.max(fastPollUntil, Date.now() + 4000);
        } catch {}
    }
    function buildMsgGroup(m) {
        // In guest support sessions the user's own messages are attributed to
        // a throwaway guest account (state.guestUserId), not the DRS user's
        // BB account (state.me.id). Treat either as "mine".
        const sid = m.sender_id ?? m.sender?.id;
        const mine = !!sid && (
            sid === state.me?.id ||
            sid === state.guestUserId
        );
        const senderName = m.sender?.display_name || m.sender_name || (mine ? T.you : T.support);
        const grp = document.createElement('div');
        grp.className = 'cw-msg-group ' + (mine ? 'mine' : 'them');
        const row = document.createElement('div'); row.className = 'cw-msg-row';
        const av = document.createElement('div'); av.className = 'cw-msg-avatar';
        if (m.sender?.avatar_url) { av.style.backgroundImage = 'url(' + m.sender.avatar_url + ')'; av.textContent = ''; }
        else av.textContent = ((senderName || '?').trim()[0] || '?').toUpperCase();
        const bubble = document.createElement('div'); bubble.className = 'cw-msg';
        bubble.textContent = m.content || m.text || '';
        row.appendChild(av); row.appendChild(bubble);
        grp.appendChild(row);
        if (!mine) {
            const name = document.createElement('div'); name.className = 'cw-msg-name';
            name.textContent = senderName;
            grp.appendChild(name);
        }
        return grp;
    }
    function isHiddenPrimer(m) {
        const c = m?.content || m?.text || '';
        if (typeof c !== 'string') return false;
        if (c.startsWith(CTX_MARKER)) return true;
        // Suppress BangBang's duplicate "handoff confirmation" second message —
        // the first one ("Of course - I am bringing in a support agent…") already
        // says the same thing. Keeps the log clean.
        if (c.startsWith('This conversation is now waiting for a support agent')) return true;
        return false;
    }
    function alreadyRendered(id) {
        return !!(id && messagesEl.querySelector('[data-msg-id="' + id + '"]'));
    }
    function appendMessage(m) {
        if (isHiddenPrimer(m)) return;
        if (alreadyRendered(m.id)) return; // DOM-based dedup, safe against race conditions
        const empty = messagesEl.querySelector('.cw-empty:not(.cw-typing)');
        if (empty) empty.remove();
        const grp = buildMsgGroup(m);
        if (m.id) grp.setAttribute('data-msg-id', m.id);
        const typing = messagesEl.querySelector('.cw-typing');
        if (typing) messagesEl.insertBefore(grp, typing); else messagesEl.appendChild(grp);
        // Detect a bot / other-side message → drop the simulated "typing" bubble.
        const sid = m.sender_id ?? m.sender?.id;
        const fromOther = !!sid && sid !== state.me?.id && sid !== state.guestUserId;
        if (fromOther) hideBotTyping();
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    async function goChatSupport() {
        const cfg = await ensureConfig();
        if (!cfg) return;
        setView('list');
        switchTab(state.tab || 'chats');
    }
    // Legacy marker: earlier versions of the widget posted a hidden DRS-context
    // "primer" message on session start. The bot interpreted that as a request
    // and tried to escalate to a human, so it's removed. Kept as a filter so
    // any leftover primers in older rooms stay hidden from the UI.
    const CTX_MARKER = '[__DRS_CTX__]';

    async function goRealtimeSupport() {
        const cfg = await ensureConfig();
        if (!cfg) return;
        // If a support ticket is already open (user hit "back" then came back),
        // reopen the same room instead of resetting to a fresh session.
        if (state.room && !state.pendingSupport) {
            openRoom(state.room);
            return;
        }
        // Fresh entry: defer the /support/guest call until the user's first send.
        state.pendingSupport = true;
        state.room = null;
        state.guestUserId = null;
        state.initialRendered = true;
        openRoomShell();
    }
    function openRoomShell() {
        // Synthesises the header/compose UI without a real room yet.
        setView('room');
        stopRoomPoll();
        const me = state.me?.display_name || state.me?.username || T.you;
        roomTitle.textContent = T.assistant + ' · ' + me;
        roomSub.textContent = T.reply_eta;
        roomAvatar.textContent = 'BB';
        roomAvatar.style.background = '#111827';
        roomAvatar.style.color = '#fff';
        roomAvatar.style.backgroundImage = '';
        messagesEl.innerHTML = '<div class="cw-empty">' + T.no_messages + '</div>';
        input.focus?.();
    }
    widget.querySelectorAll('[data-cw-choose]').forEach(b => b.addEventListener('click', () => {
        // Single "Start Conversation" flow — opens a live support ticket.
        goRealtimeSupport();
    }));


    async function switchTab(t) {
        state.tab = t;
        tabs.forEach(x => x.classList.toggle('active', x.dataset.cwTab === t));
        searchWrap.style.display = t === 'contacts' ? 'flex' : 'none';
        listBody.innerHTML = '<div class="cw-empty"><span class="cw-spinner"></span></div>';
        try {
            if (t === 'chats') {
                const r = await j('{{ route('chat.rooms') }}');
                state.rooms = r.rooms || [];
                renderRooms();
            } else {
                await loadContacts();
            }
        } catch (e) {
            listBody.innerHTML = '<div class="cw-empty">' + (e.body?.message || 'Failed to load.') + '</div>';
        }
    }
    tabs.forEach(t => t.addEventListener('click', () => switchTab(t.dataset.cwTab)));

    function renderRooms() {
        if (!state.rooms.length) {
            listBody.innerHTML = '<div class="cw-empty">No chats yet. Open <strong>Contacts</strong> to start one.</div>';
            return;
        }
        const list = document.createElement('div'); list.className = 'cw-list';
        for (const r of state.rooms) {
            const other = (r.members || []).find(m => (m.id ?? m.user_id) !== state.me?.id) || {};
            const item = document.createElement('div'); item.className = 'cw-item';
            const display = r.name || other.display_name || 'Room ' + r.id;
            item.appendChild(avatarEl({ display_name: display, avatar_url: r.avatar_url || other.avatar_url, is_online: other.is_online }));
            const meta = document.createElement('div'); meta.className = 'cw-meta';
            meta.innerHTML = '<div class="cw-name"></div><div class="cw-msub"></div>';
            meta.querySelector('.cw-name').textContent = display;
            meta.querySelector('.cw-msub').textContent = r.last_message?.content || (r.type === 'direct' ? 'Direct message' : (r.type === 'support' ? 'Support' : 'Group chat'));
            item.appendChild(meta);
            const right = document.createElement('div'); right.style.textAlign = 'right';
            if (r.last_message?.created_at) { const t = document.createElement('div'); t.className = 'cw-time'; t.textContent = relTime(r.last_message.created_at); right.appendChild(t); }
            if (r.unread_count > 0) { const u = document.createElement('div'); u.className = 'cw-unread'; u.textContent = r.unread_count > 99 ? '99+' : r.unread_count; right.appendChild(u); }
            item.appendChild(right);
            item.addEventListener('click', () => openRoom(r));
            list.appendChild(item);
        }
        listBody.innerHTML = ''; listBody.appendChild(list);
    }
    let searchTimer;
    async function loadContacts() {
        const q = searchInput.value.trim();
        const url = new URL('{{ route('chat.users') }}', location.origin);
        if (q) url.searchParams.set('q', q);
        const r = await j(url.toString());
        state.contacts = (r.users || []).filter(u => u.id !== state.me?.id);
        renderContacts();
    }
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadContacts, 250);
    });
    function renderContacts() {
        if (!state.contacts.length) {
            listBody.innerHTML = '<div class="cw-empty">No contacts found.</div>';
            return;
        }
        const list = document.createElement('div'); list.className = 'cw-list';
        for (const u of state.contacts) {
            const item = document.createElement('div'); item.className = 'cw-item';
            item.appendChild(avatarEl(u));
            const meta = document.createElement('div'); meta.className = 'cw-meta';
            meta.innerHTML = '<div class="cw-name"></div><div class="cw-msub"></div>';
            meta.querySelector('.cw-name').textContent = u.display_name || u.username;
            meta.querySelector('.cw-msub').textContent = u.is_online ? 'Online' : (u.last_seen ? 'Last seen ' + relTime(u.last_seen) : 'Offline');
            item.appendChild(meta);
            item.addEventListener('click', () => startDirect(u));
            list.appendChild(item);
        }
        listBody.innerHTML = ''; listBody.appendChild(list);
    }
    async function startDirect(contact) {
        showStatus('Opening chat…', '');
        try {
            // Use BangBang internal user id; external_id lookup isn't supported
            // by BangBang's on_behalf_of / member_external_ids path.
            const room = await j('/chat/direct/' + encodeURIComponent(contact.id), { method: 'POST' });
            showStatus('', ''); openRoom(room);
        } catch (e) {
            showStatus(e.body?.message || 'Could not open chat.', 'err');
        }
    }
    let roomPollTimer = null;
    let fastPollUntil = 0;
    let fastPollTimer = null;
    function stopRoomPoll() {
        if (roomPollTimer) { clearInterval(roomPollTimer); roomPollTimer = null; }
        if (fastPollTimer) { clearTimeout(fastPollTimer); fastPollTimer = null; }
    }
    function startRoomPollFallback() {
        stopRoomPoll();
        roomPollTimer = setInterval(() => {
            if (!state.room) return stopRoomPoll();
            refreshMessages();
        }, 400);
    }
    function burstPoll() {
        // Poll every 100 ms so bot replies feel instant. Window auto-extends
        // inside refreshMessages() whenever new messages arrive.
        fastPollUntil = Math.max(fastPollUntil, Date.now() + 8000);
        if (fastPollTimer) return;
        (function tick() {
            fastPollTimer = null;
            if (Date.now() > fastPollUntil) return;
            if (!state.room) return;
            refreshMessages().finally(() => { fastPollTimer = setTimeout(tick, 100); });
        })();
    }
    async function openRoom(room) {
        state.room = room;
        state.lastMsgId = 0;         // (legacy; no longer used by polling)
        state.initialRendered = false; // force refreshMessages to do full render on first tick
        setView('room');
        stopRoomPoll();
        wsSend({ type: 'room.subscribe', room_id: room.id });
        wsSend({ type: 'read.mark', room_id: room.id });
        const other = (room.members || []).find(m => (m.id ?? m.user_id) !== state.me?.id) || {};
        let title, avatarLabel;
        if (room.type === 'support') {
            const me = state.me?.display_name || state.me?.username || T.you;
            title = T.assistant + ' · ' + me;
            avatarLabel = 'BB';
        } else {
            title = room.name || other.display_name || 'Room ' + room.id;
            avatarLabel = initials(title);
        }
        roomTitle.textContent = title;
        roomSub.textContent = room.type === 'support' ? T.reply_eta : (room.type === 'direct' ? (other.is_online ? T.online : T.offline) : T.group);
        roomAvatar.textContent = avatarLabel;
        roomAvatar.style.backgroundImage = '';
        // Black avatar for the BangBang assistant, default green tint otherwise.
        if (room.type === 'support') {
            roomAvatar.style.background = '#111827';
            roomAvatar.style.color = '#fff';
        } else {
            roomAvatar.style.background = '';
            roomAvatar.style.color = '';
        }
        if (room.avatar_url) { roomAvatar.style.backgroundImage = 'url(' + room.avatar_url + ')'; roomAvatar.textContent = ''; }
        messagesEl.innerHTML = '<div class="cw-empty"><span class="cw-spinner"></span></div>';
        try {
            const r = await j('/chat/rooms/' + room.id + '/messages');
            renderMessages(r.messages || []);
        } catch (e) {
            messagesEl.innerHTML = '<div class="cw-empty">' + (e.body?.message || T.failed_load_messages) + '</div>';
        }
        startRoomPollFallback();
    }
    function renderMessages(msgs) {
        messagesEl.innerHTML = '';
        // Filter out hidden priming messages but keep their ids in the watermark below.
        const visible = (msgs || []).filter(m => !isHiddenPrimer(m));
        if (!visible.length) {
            messagesEl.innerHTML = '<div class="cw-empty">' + T.no_messages + '</div>';
            // Still advance the watermark so incremental polls don't refetch the primer.
            for (const m of msgs || []) if (m.id && m.id > (state.lastMsgId || 0)) state.lastMsgId = m.id;
            return;
        }
        // Sort deterministically: oldest first (top) → newest last (bottom).
        const ordered = visible.slice().sort((a, b) => {
            if (typeof a.id === 'number' && typeof b.id === 'number') return a.id - b.id;
            const at = a.created_at ? new Date(a.created_at).getTime() : 0;
            const bt = b.created_at ? new Date(b.created_at).getTime() : 0;
            return at - bt;
        });
        // Insert a "Today" separator once, at the top of today's messages.
        const today = new Date().toDateString();
        let addedTodaySep = false;
        let maxId = 0;
        for (const m of ordered) {
            const day = m.created_at ? new Date(m.created_at).toDateString() : today;
            if (!addedTodaySep && day === today) {
                const sep = document.createElement('div'); sep.className = 'cw-day-sep'; sep.textContent = T.today;
                messagesEl.appendChild(sep);
                addedTodaySep = true;
            }
            const grp = buildMsgGroup(m);
            if (m.id) grp.setAttribute('data-msg-id', m.id);
            messagesEl.appendChild(grp);
            if (m.id && m.id > maxId) maxId = m.id;
        }
        if (maxId) state.lastMsgId = Math.max(state.lastMsgId || 0, maxId);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
    input.addEventListener('input', () => {
        // Enable send if we have a room OR we're about to initialise one.
        sendBtn.disabled = !input.value.trim() || (!state.room && !state.pendingSupport);
        input.style.height = 'auto';
        input.style.height = Math.min(96, input.scrollHeight) + 'px';
        if (state.room && Date.now() - typingSentAt > 3000) {
            wsSend({ type: 'typing.start', room_id: state.room.id });
            typingSentAt = Date.now();
        }
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); if (!sendBtn.disabled) sendBtn.click(); }
    });
    sendBtn.addEventListener('click', async () => {
        const text = input.value.trim();
        if (!text) return;
        if (!state.room && !state.pendingSupport) return;
        const clientMsgId = (crypto.randomUUID?.() || (Date.now() + '-' + Math.random().toString(36).slice(2)));
        sendBtn.disabled = true;
        try {
            // First send of a support session: create the /support/guest ticket
            // AND include this message as the opener (bot then replies to it).
            if (state.pendingSupport) {
                const resp = await j('{{ route('chat.support.init') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text }),
                });
                state.pendingSupport = false;
                state.room = resp.room || null;
                state.guestUserId = resp.user?.id || null; // for "mine" detection
                input.value = ''; input.dispatchEvent(new Event('input'));
                messagesEl.innerHTML = '';
                startRoomPollFallback();
                await refreshMessages();
                showBotTyping();
                burstPoll();
                return;
            }
            const sent = await j('/chat/rooms/' + state.room.id + '/messages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: text, client_msg_id: clientMsgId }),
            });
            input.value = ''; input.dispatchEvent(new Event('input'));
            if (sent && sent.id) appendMessage(sent);
            showBotTyping();
            burstPoll();
        } catch (e) {
            showStatus(e.body?.message || T.failed_send, 'err');
        } finally { sendBtn.disabled = false; }
    });

    async function open() {
        panel.classList.add('open'); toggle.setAttribute('aria-expanded', 'true');
        setView('landing');
        const cfg = await ensureConfig();
        if (!cfg) return;
    }
    function close() {
        panel.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false');
        try { ws?.close(); } catch {} ws = null;
        if (wsHeartbeat) { clearInterval(wsHeartbeat); wsHeartbeat = null; }
        stopRoomPoll();
    }
    toggle.addEventListener('click', () => panel.classList.contains('open') ? close() : open());
    // Right icon: close the chat via the feedback survey (only when we're in a room)
    endBtn.addEventListener('click', () => {
        if (state.view === 'room') { setView('feedback'); return; }
        close();
    });
    // Left arrow: back one step (feedback → room, room/list → landing, landing → close)
    backBtn.addEventListener('click', () => {
        if (state.view === 'feedback') { setView('room'); return; }
        if (state.view === 'room') { setView('landing'); return; }
        if (state.view === 'list') { setView('landing'); return; }
    });

    // Feedback interactions
    let feedbackRating = 0;
    feedbackEmojis.forEach(b => b.addEventListener('click', () => {
        feedbackRating = Number(b.getAttribute('data-cw-rating')) || 0;
        feedbackEmojis.forEach(x => x.classList.toggle('selected', x === b));
    }));
    feedbackSubmitBtn.addEventListener('click', async () => {
        const comment = (feedbackInput.value || '').trim();
        if (!feedbackRating && !comment) { showStatus(T.fb_pick_rating, 'err'); return; }
        feedbackSubmitBtn.disabled = true;
        const closedRoomId = state.room?.id;
        try {
            if (closedRoomId) {
                const stars = feedbackRating ? '⭐'.repeat(feedbackRating) : '';
                const body = ['[Feedback]', stars ? 'Rating: ' + stars + ' (' + feedbackRating + '/5)' : null, comment ? '\n' + comment : null]
                    .filter(Boolean).join(' ');
                await j('/chat/rooms/' + closedRoomId + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content: body, client_msg_id: 'fb-' + Date.now() + '-' + Math.random().toString(36).slice(2) }),
                });
                // Archive the support ticket so /support/start creates a fresh
                // room the next time the user opens the widget.
                try { await j('/chat/rooms/' + closedRoomId, { method: 'DELETE' }); } catch {}
            }
            showStatus(T.fb_thanks, 'ok');
            feedbackInput.value = '';
            feedbackRating = 0;
            feedbackEmojis.forEach(x => x.classList.remove('selected'));
            state.room = null;
            setTimeout(() => {
                showStatus('', '');
                setView('landing');
                close(); // dismiss the widget; next open starts a new session
            }, 1000);
        } catch (e) {
            showStatus(e.body?.message || 'Could not send feedback.', 'err');
        } finally {
            feedbackSubmitBtn.disabled = false;
        }
    });
    feedbackInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); feedbackSubmitBtn.click(); }
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && panel.classList.contains('open')) close(); });

    async function pollUnread() {
        try { const r = await j('{{ route('chat.unread') }}'); setBadge(r.unread || 0); } catch {}
    }
    pollUnread(); setInterval(pollUnread, 30000);
    window.ChatWidget = { open, close, setUnread: setBadge };
})();
</script>
