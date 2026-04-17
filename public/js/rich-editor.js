/**
 * Minimal rich-text editor built on contenteditable + document.execCommand.
 * Zero external dependencies.
 *
 * Usage: call initRichEditor({ editorId, inputId, formId })
 */
function initRichEditor({ editorId, inputId, formId }) {
    var editor = document.getElementById(editorId);
    var input  = document.getElementById(inputId);
    var form   = document.getElementById(formId);

    if (!editor || !input || !form) return;

    // Make editable
    editor.contentEditable = 'true';
    editor.classList.add('rich-editor__body');

    // ── Toolbar ──────────────────────────────────────────────────────────────

    var toolbar = document.createElement('div');
    toolbar.className = 'rich-editor__toolbar';

    var buttons = [
        { cmd: 'bold',              label: '<b>B</b>',            title: 'Bold'            },
        { cmd: 'italic',            label: '<i>I</i>',            title: 'Italic'          },
        { cmd: 'underline',         label: '<u>U</u>',            title: 'Underline'       },
        { cmd: 'strikeThrough',     label: '<s>S</s>',            title: 'Strikethrough'   },
        { sep: true },
        { cmd: 'formatBlock', val: 'h2',         label: 'H2',    title: 'Heading 2'       },
        { cmd: 'formatBlock', val: 'h3',         label: 'H3',    title: 'Heading 3'       },
        { cmd: 'formatBlock', val: 'p',          label: '¶',     title: 'Paragraph'       },
        { sep: true },
        { cmd: 'insertOrderedList',   label: '1.',  title: 'Numbered list'   },
        { cmd: 'insertUnorderedList', label: '•',   title: 'Bullet list'     },
        { sep: true },
        { cmd: 'formatBlock', val: 'blockquote', label: '❝',    title: 'Blockquote'      },
        { cmd: 'createLink',          label: '🔗',  title: 'Insert link'     },
        { cmd: 'unlink',              label: '🔗̶', title: 'Remove link'      },
        { sep: true },
        { cmd: 'removeFormat',        label: '✕',   title: 'Clear formatting' },
    ];

    buttons.forEach(function (def) {
        if (def.sep) {
            var sep = document.createElement('span');
            sep.className = 'rich-editor__sep';
            toolbar.appendChild(sep);
            return;
        }

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'rich-editor__btn';
        btn.title = def.title;
        btn.innerHTML = def.label;
        btn.dataset.cmd = def.cmd;
        if (def.val) btn.dataset.val = def.val;

        btn.addEventListener('mousedown', function (e) {
            // Prevent editor from losing focus
            e.preventDefault();

            if (def.cmd === 'createLink') {
                var url = prompt('Enter URL:', 'https://');
                if (url) document.execCommand('createLink', false, url);
            } else if (def.val) {
                document.execCommand(def.cmd, false, def.val);
            } else {
                document.execCommand(def.cmd, false, null);
            }

            updateActiveStates();
            editor.focus();
        });

        toolbar.appendChild(btn);
    });

    // Insert toolbar before editor in the DOM
    editor.parentNode.insertBefore(toolbar, editor);

    // ── Active state ─────────────────────────────────────────────────────────

    function updateActiveStates() {
        toolbar.querySelectorAll('.rich-editor__btn').forEach(function (btn) {
            var cmd = btn.dataset.cmd;
            try {
                if (['bold','italic','underline','strikeThrough'].indexOf(cmd) !== -1) {
                    btn.classList.toggle('rich-editor__btn--active', document.queryCommandState(cmd));
                }
            } catch (e) {}
        });
    }

    editor.addEventListener('keyup',  updateActiveStates);
    editor.addEventListener('mouseup', updateActiveStates);

    // ── Sync content on submit ────────────────────────────────────────────────

    form.addEventListener('submit', function () {
        input.value = editor.innerHTML;
    });

    // ── Paste: strip external formatting, keep plain text ────────────────────

    editor.addEventListener('paste', function (e) {
        e.preventDefault();
        var text = (e.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, text);
    });

    // Initial focus style
    editor.addEventListener('focus', function () { editor.classList.add('rich-editor__body--focus'); });
    editor.addEventListener('blur',  function () { editor.classList.remove('rich-editor__body--focus'); });
}
