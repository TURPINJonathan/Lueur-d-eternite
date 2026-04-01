(() => {
  const EDITOR_SELECTOR = '.js-email-template-editor';

  function debounce(fn, delay) {
    let timer = null;
    return (...args) => {
      if (timer) clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  }

  async function renderPreview(textarea, state) {
    const url = textarea.dataset.previewUrl;
    const kind = textarea.dataset.previewKind || 'admin';
    if (!url) return;

    state.status.textContent = 'Aperçu en cours...';

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          kind,
          template: textarea.value,
        }),
      });

      const payload = await response.json().catch(() => ({}));
      if (!response.ok) {
        const detail = payload?.detail ? `\n${payload.detail}` : '';
        throw new Error((payload?.message || 'Erreur de rendu') + detail);
      }

      state.iframe.srcdoc = payload.html || '';
      state.status.textContent = 'Aperçu à jour';
      state.error.textContent = '';
    } catch (error) {
      state.status.textContent = 'Aperçu indisponible';
      state.error.textContent = error instanceof Error ? error.message : 'Erreur inconnue';
    }
  }

  function createPreviewNodes(textarea) {
    const row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexWrap = 'wrap';
    row.style.gap = '14px';
    row.style.marginTop = '12px';
    row.style.alignItems = 'stretch';

    const editorCol = document.createElement('div');
    editorCol.style.flex = '1 1 540px';
    editorCol.style.minWidth = '320px';

    const previewCol = document.createElement('div');
    previewCol.style.flex = '1 1 540px';
    previewCol.style.minWidth = '320px';

    const wrapper = document.createElement('div');
    wrapper.style.height = '100%';
    wrapper.style.border = '1px solid rgba(43,43,43,.12)';
    wrapper.style.borderRadius = '10px';
    wrapper.style.overflow = 'hidden';
    wrapper.style.background = '#fff';

    const header = document.createElement('div');
    header.style.display = 'flex';
    header.style.justifyContent = 'space-between';
    header.style.alignItems = 'center';
    header.style.padding = '8px 12px';
    header.style.background = '#f4efe7';
    header.style.borderBottom = '1px solid rgba(43,43,43,.08)';
    header.style.fontSize = '12px';

    const title = document.createElement('strong');
    title.textContent = 'Aperçu live';

    const status = document.createElement('span');
    status.textContent = 'Initialisation...';

    header.appendChild(title);
    header.appendChild(status);

    const error = document.createElement('pre');
    error.style.display = 'block';
    error.style.margin = '0';
    error.style.padding = '10px 12px';
    error.style.color = '#8b1d1d';
    error.style.background = '#fff5f5';
    error.style.fontSize = '12px';
    error.style.whiteSpace = 'pre-wrap';

    const iframe = document.createElement('iframe');
    iframe.style.width = '100%';
    iframe.style.height = '560px';
    iframe.style.border = '0';
    iframe.setAttribute('title', 'Aperçu template email');

    wrapper.appendChild(header);
    wrapper.appendChild(error);
    wrapper.appendChild(iframe);

    textarea.style.height = '560px';
    textarea.style.minHeight = '560px';
    textarea.style.maxHeight = '560px';
    textarea.style.overflowY = 'auto';
    textarea.style.resize = 'none';

    const parent = textarea.parentElement;
    if (parent) {
      parent.insertBefore(row, textarea);
      editorCol.appendChild(textarea);
      previewCol.appendChild(wrapper);
      row.appendChild(editorCol);
      row.appendChild(previewCol);
    } else {
      textarea.insertAdjacentElement('afterend', wrapper);
    }

    return { wrapper, iframe, status, error };
  }

  function initEditor(textarea) {
    if (textarea.dataset.previewMounted === '1') return;
    textarea.dataset.previewMounted = '1';

    const state = createPreviewNodes(textarea);
    const debouncedRender = debounce(() => renderPreview(textarea, state), 300);

    textarea.addEventListener('input', debouncedRender);
    renderPreview(textarea, state);
  }

  function boot() {
    document.querySelectorAll(EDITOR_SELECTOR).forEach((textarea) => {
      if (textarea instanceof HTMLTextAreaElement) initEditor(textarea);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
