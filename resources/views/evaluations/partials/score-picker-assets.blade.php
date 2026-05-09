@once
<style>
.score-picker { position: relative; display: inline-block; }
.score-picker-toggle {
    display: flex; align-items: center; justify-content: space-between;
    width: 100%; cursor: pointer; text-align: left;
    background: #fff; padding: 6px 10px;
}
.score-picker-toggle:focus { outline: 2px solid #3b82f6; outline-offset: 1px; }
.score-picker .sp-caret { margin-left: 8px; color: #6b7280; font-size: 10px; }
.score-picker-list {
    position: fixed; z-index: 1000;
    background: #fff; border: 1px solid #d1d5db; border-radius: 6px;
    overflow-y: auto; overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    padding: 4px 0;
}
.score-picker-list .sp-option {
    padding: 6px 14px; cursor: pointer; font-size: 14px; user-select: none;
}
.score-picker-list .sp-option:hover { background: #eff6ff; }
.score-picker-list .sp-option.selected { background: #dbeafe; font-weight: 600; }
.score-picker.invalid .score-picker-toggle { border-color: #dc2626; }
@media (max-width: 480px) {
    .score-picker-list { max-height: 240px; }
}
</style>
<script>
(function () {
    function closeAll() {
        document.querySelectorAll('.score-picker-list').forEach(function (l) { l.hidden = true; });
    }
    function positionList(toggle, list) {
        var rect = toggle.getBoundingClientRect();
        var viewport = window.innerHeight;
        var spaceBelow = viewport - rect.bottom - 8;
        var spaceAbove = rect.top - 8;
        var desired = 280;
        list.style.left = rect.left + 'px';
        list.style.minWidth = rect.width + 'px';
        list.style.right = '';
        if (spaceBelow >= 160 || spaceBelow >= spaceAbove) {
            list.style.top = (rect.bottom + 2) + 'px';
            list.style.bottom = '';
            list.style.maxHeight = Math.max(120, Math.min(desired, spaceBelow)) + 'px';
        } else {
            list.style.top = '';
            list.style.bottom = (viewport - rect.top + 2) + 'px';
            list.style.maxHeight = Math.max(120, Math.min(desired, spaceAbove)) + 'px';
        }
    }
    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('.score-picker-toggle');
        if (toggle) {
            var picker = toggle.parentElement;
            var list = picker.querySelector('.score-picker-list');
            var willOpen = list.hidden;
            closeAll();
            if (willOpen) {
                list.hidden = false;
                positionList(toggle, list);
                var sel = list.querySelector('.sp-option.selected');
                if (sel) list.scrollTop = Math.max(0, sel.offsetTop - 60);
            }
            return;
        }
        var opt = e.target.closest('.sp-option');
        if (opt) {
            var list = opt.parentElement;
            var picker = list.parentElement;
            var hidden = picker.querySelector('input[type=hidden]');
            var label = picker.querySelector('.sp-value');
            hidden.value = opt.dataset.value || '';
            label.textContent = opt.dataset.value !== '' ? opt.dataset.value : '— Select —';
            list.querySelectorAll('.sp-option.selected').forEach(function (o) { o.classList.remove('selected'); });
            opt.classList.add('selected');
            picker.classList.remove('invalid');
            list.hidden = true;
            return;
        }
        if (!e.target.closest('.score-picker')) closeAll();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll();
    });
    window.addEventListener('resize', closeAll);
    window.addEventListener('scroll', function (e) {
        if (e.target.closest && e.target.closest('.score-picker-list')) return;
        closeAll();
    }, true);
    document.addEventListener('submit', function (e) {
        var form = e.target;
        var bad = false;
        form.querySelectorAll('.score-picker input[type=hidden][data-required]').forEach(function (input) {
            if (!input.value) {
                input.parentElement.classList.add('invalid');
                if (!bad) input.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                bad = true;
            }
        });
        if (bad) { e.preventDefault(); }
    });
})();
</script>
@endonce
