(() => {
  'use strict';

  const UPLOAD_TIMEOUT_MS = 150_000;
  const TRASH_ICON = `
    <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <polyline points="3 6 5 6 21 6"></polyline>
      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
    </svg>
  `;

  function createUploadId() {
    return Array.from(
      crypto.getRandomValues(new Uint8Array(16)),
      byte => byte.toString(16).padStart(2, '0')
    ).join('');
  }

  document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-guest-snap]');
    if (!root) return;

    const form = root.querySelector('form');
    const fileInput = root.querySelector('[data-guest-snap-files]');
    const preview = root.querySelector('[data-guest-snap-preview]');
    const status = root.querySelector('[data-guest-snap-error]');
    const submitButton = root.querySelector('[data-guest-snap-submit]');
    const progress = root.querySelector('[data-guest-snap-progress]');
    const nameInput = form.elements.name;
    const phoneInput = form.elements.phone;
    const privacyInput = form.elements.privacy;
    const selection = root.querySelector('[data-guest-snap-selection]');
    const selectionCount = root.querySelector('[data-guest-snap-selection-count]');
    const selectionSize = root.querySelector('[data-guest-snap-selection-size]');
    const overBadge = root.querySelector('[data-guest-snap-over-badge]');
    const anotherButton = root.querySelector('[data-guest-snap-another]');
    const viewer = root.querySelector('[data-guest-snap-viewer]');
    const viewerImage = root.querySelector('[data-guest-snap-viewer-image]');
    const viewerReason = root.querySelector('[data-guest-snap-viewer-reason]');
    const maxFiles = Number(root.dataset.maxFiles) || 20;
    const maxBytes = Number(root.dataset.maxBytes) || 15 * 1024 * 1024;

    let items = [];
    let busy = false;
    let batchId = createUploadId();
    let viewerIndex = -1;

    function formatPhone(value) {
      const digits = String(value || '').replace(/\D/g, '').slice(0, 11);
      if (digits.length <= 3) return digits;
      if (digits.length <= 7) return `${digits.slice(0, 3)}-${digits.slice(3)}`;
      return `${digits.slice(0, 3)}-${digits.slice(3, 7)}-${digits.slice(7)}`;
    }

    function phoneDigits() {
      return phoneInput.value.replace(/\D/g, '');
    }

    function itemState(item) {
      if (item.done) return 'success';
      if (item.error) return 'error';
      if (item.phase) return 'uploading';
      return 'pending';
    }

    function shortReason(message) {
      if (!message) return '저장을 확인하지 못했습니다.';
      const first = String(message).split(/[·\n]/)[0].trim();
      return first || '저장을 확인하지 못했습니다.';
    }

    function showMessage(text, kind = 'info') {
      status.dataset.kind = kind;
      status.textContent = text;
      status.hidden = !text;
    }

    function updateSelectionSummary() {
      const totalBytes = items.reduce((sum, item) => sum + item.file.size, 0);
      const overCount = items.length > maxFiles;
      const overSize = items.some(item => item.file.size > maxBytes);

      if (!items.length) {
        selection.hidden = true;
        if (selectionCount) selectionCount.textContent = '';
        if (selectionSize) selectionSize.textContent = '';
        if (overBadge) overBadge.hidden = true;
        if (selectionCount) selectionCount.classList.remove('isOver');
        if (selectionSize) selectionSize.classList.remove('isOver');
        return;
      }

      selection.hidden = false;
      if (selectionCount) {
        selectionCount.textContent = `${items.length}/${maxFiles}장 선택`;
        selectionCount.classList.toggle('isOver', overCount);
      }
      if (overBadge) overBadge.hidden = !overCount;
      if (selectionSize) {
        selectionSize.textContent = `${(totalBytes / 1024 / 1024).toFixed(1)}MB`;
        selectionSize.classList.toggle('isOver', overSize);
      }
    }

    function openViewer(index) {
      if (!viewer || !items[index]) return;
      viewerIndex = index;
      const item = items[index];
      viewerImage.src = item.url;
      viewerImage.alt = `사진 ${index + 1}`;

      if (item.error) {
        viewerReason.hidden = false;
        viewerReason.textContent = shortReason(item.error);
      } else if (item.phase) {
        viewerReason.hidden = false;
        viewerReason.textContent = item.phase;
      } else {
        viewerReason.hidden = true;
        viewerReason.textContent = '';
      }

      // main(z-index:2) 안에 있으면 sticky 헤더(20) 아래로 깔림 → body로 올려 전체 덮기
      if (viewer.parentElement !== document.body) {
        document.body.appendChild(viewer);
      }
      viewer.hidden = false;
      viewer.setAttribute('aria-hidden', 'false');
      document.body.classList.add('guestSnapViewerOpen');
    }

    function closeViewer() {
      if (!viewer) return;
      viewer.hidden = true;
      viewer.setAttribute('aria-hidden', 'true');
      viewerIndex = -1;
      viewerImage.removeAttribute('src');
      viewerReason.hidden = true;
      viewerReason.textContent = '';
      document.body.classList.remove('guestSnapViewerOpen');
    }

    function renderPreview() {
      preview.replaceChildren();
      preview.hidden = !items.length;
      updateSelectionSummary();

      if (!busy) {
        submitButton.disabled = root.dataset.canUpload !== '1' || !items.some(item => !item.done);
      }

      items.forEach((item, index) => {
        const state = itemState(item);
        const thumbnail = document.createElement('li');
        thumbnail.className = 'guestSnapDrop__thumb';
        if (state === 'success') thumbnail.classList.add('isSuccess');
        if (state === 'error') thumbnail.classList.add('isError');
        if (state === 'uploading') thumbnail.classList.add('isUploading');
        thumbnail.dataset.state = state;

        const image = document.createElement('img');
        image.src = item.url;
        image.alt = `사진 ${index + 1}`;
        image.onerror = () => { image.hidden = true; };

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'guestSnapDrop__remove';
        removeButton.innerHTML = TRASH_ICON;
        removeButton.setAttribute('aria-label', `사진 ${index + 1} 삭제`);
        removeButton.disabled = busy;
        removeButton.addEventListener('click', event => {
          event.preventDefault();
          event.stopPropagation();
          URL.revokeObjectURL(item.url);
          items = items.filter(candidate => candidate !== item);
          if (viewerIndex === index) closeViewer();
          else if (viewerIndex > index) viewerIndex -= 1;
          renderPreview();
        });

        thumbnail.append(image, removeButton);

        if (state === 'error') {
          const badge = document.createElement('span');
          badge.className = 'guestSnapDrop__errorBadge';
          badge.textContent = '!';
          badge.setAttribute('aria-hidden', 'true');
          thumbnail.append(badge);
        }

        thumbnail.addEventListener('click', () => openViewer(index));
        preview.append(thumbnail);
      });
    }

    function setBusy(value) {
      busy = value;
      submitButton.disabled = value || !items.some(item => !item.done);
      submitButton.textContent = value ? '사진을 저장하고 있어요…' : '사진 올리기';
      form.setAttribute('aria-busy', String(value));
      anotherButton.hidden = value || !items.length || items.some(item => !item.done);
      fileInput.disabled = value;
      nameInput.disabled = value;
      phoneInput.disabled = value;
      if (privacyInput) privacyInput.disabled = value;
      progress.hidden = !value;
      renderPreview();
    }

    function uploadPhoto(item, index, total) {
      item.error = '';
      item.phase = '전송 준비 중';
      renderPreview();
      showMessage(`${index + 1}/${total}장 · 전송 준비 중`);

      return new Promise(resolve => {
        const request = new XMLHttpRequest();
        request.open('POST', form.action);
        request.timeout = UPLOAD_TIMEOUT_MS;

        const body = new FormData();
        body.append('photo', item.file);
        body.append('name', nameInput.value.trim());
        body.append('phone', phoneDigits());
        body.append('privacy', privacyInput && privacyInput.checked ? '1' : '');
        body.append('csrf', form.elements.csrf.value);
        body.append('batch', batchId);
        body.append('item', item.id);

        request.upload.onprogress = event => {
          if (!event.lengthComputable) return;
          progress.value = Math.round((event.loaded / event.total) * 100);
          const saving = progress.value === 100 ? ' · 저장 확인 중' : '';
          item.phase = progress.value === 100 ? '저장 확인 중' : `${progress.value}% 전송`;
          showMessage(`${index + 1}/${total}장 · ${progress.value}% 전송${saving}`);
          renderPreview();
        };

        request.onload = () => {
          let response;
          try {
            const payload = JSON.parse(request.responseText);
            response = payload && typeof payload === 'object' ? payload : {};
          } catch (_) {
            response = {};
          }

          item.done = request.status === 200 && response.ok === true;
          item.phase = '';
          item.stopBatch = request.status === 429 || request.status === 403 || request.status === 503;
          item.error = item.done
            ? ''
            : typeof response.message === 'string'
              ? response.message
              : '저장을 확인하지 못했습니다. 다시 시도해주세요.';
          resolve();
        };

        const handleConnectionFailure = () => {
          item.phase = '';
          item.stopBatch = true;
          item.error = '연결이 끊겼습니다. 같은 화면에서 재시도해주세요.';
          resolve();
        };

        request.onerror = handleConnectionFailure;
        request.ontimeout = handleConnectionFailure;
        request.onabort = handleConnectionFailure;
        try {
          request.send(body);
        } catch (_) {
          handleConnectionFailure();
        }
      });
    }

    function showUploadResult() {
      const succeeded = items.filter(item => item.done).length;
      const failed = items.length - succeeded;

      if (!failed) {
        showMessage(`${succeeded}장 모두 비공개로 저장되었습니다. 감사합니다!`, 'success');
        submitButton.textContent = '업로드 완료';
        return;
      }

      showMessage(
        succeeded
          ? `${failed}장 실패 · 실패한 사진만 다시 시도해주세요.`
          : `${failed}장 실패 · 다시 시도해주세요.`,
        'error'
      );
      submitButton.textContent = '실패한 사진 재시도';
    }

    function openLegalModal(kind) {
      const modal = document.querySelector(`[data-guestbook-legal="${kind}"]`);
      if (!modal) return;
      if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
      }
      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
      requestAnimationFrame(() => {
        modal.classList.add('isOpen');
      });
    }

    function closeLegalModal(modal) {
      if (!modal) return;
      modal.classList.remove('isOpen');
      window.setTimeout(() => {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
      }, 320);
    }

    phoneInput.addEventListener('input', () => {
      const formatted = formatPhone(phoneInput.value);
      if (phoneInput.value !== formatted) {
        phoneInput.value = formatted;
      }
    });

    fileInput.addEventListener('change', () => {
      if (busy) return;

      if (items.length && items.every(item => item.done)) {
        items.forEach(item => URL.revokeObjectURL(item.url));
        items = [];
        batchId = createUploadId();
      }

      let warning = '';
      let added = 0;

      Array.from(fileInput.files || []).forEach(file => {
        // 초과 선택은 허용. 업로드는 막지 않고 selection에서만 표시.
        if (!file.size || file.size > maxBytes) {
          warning = '사진은 장당 15MB 이하로 선택해주세요.';
          return;
        }

        if (!/\.(jpe?g|png|webp)$/i.test(file.name)) {
          warning = 'JPEG·PNG·WebP 사진을 선택해주세요. HEIC는 JPEG로 변환해주세요.';
          return;
        }

        items.push({
          file,
          id: createUploadId(),
          url: URL.createObjectURL(file),
          done: false,
          error: '',
          phase: '',
        });
        added += 1;
      });

      fileInput.value = '';
      if (warning) {
        showMessage(warning, 'error');
      } else if (added && items.length > maxFiles) {
        showMessage(`${items.length}/${maxFiles}장 · 권장 장수를 초과했습니다.`, 'error');
      } else {
        showMessage('');
      }
      submitButton.textContent = '사진 올리기';
      anotherButton.hidden = true;
      renderPreview();
    });

    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (busy || root.dataset.canUpload !== '1') return;

      if (!items.length) {
        showMessage('사진을 선택해주세요.');
        return;
      }

      const name = nameInput.value.trim();
      const phone = phoneDigits();

      if (!/^[\p{L}\p{M}\p{N} .’'_-]{1,20}$/u.test(name) || !/[\p{L}\p{N}]/u.test(name)) {
        showMessage('성함을 1~20자로 입력해주세요. 특수문자는 사용할 수 없습니다.', 'error');
        nameInput.focus();
        return;
      }

      if (!/^[0-9]{8,15}$/.test(phone)) {
        showMessage('연락처는 숫자 8~15자리로 입력해주세요.', 'error');
        phoneInput.focus();
        return;
      }

      if (!privacyInput || !privacyInput.checked) {
        showMessage('개인정보 처리방침과 이용약관에 동의해주세요.', 'error');
        if (privacyInput) privacyInput.focus();
        return;
      }

      const pending = items.filter(item => !item.done);
      if (!pending.length) {
        showMessage('모든 사진이 저장되었습니다. 새 사진을 선택할 수 있어요.');
        return;
      }

      setBusy(true);

      try {
        for (let index = 0; index < pending.length; index++) {
          progress.value = 0;
          await uploadPhoto(pending[index], index, pending.length);
          renderPreview();

          if (pending[index].stopBatch) {
            pending.slice(index + 1).forEach(item => {
              item.error = '전송 대기 · 연결 상태와 안내를 확인한 뒤 다시 시도해주세요.';
            });
            break;
          }
        }
      } finally {
        setBusy(false);
        showUploadResult();
      }
    });

    anotherButton.addEventListener('click', () => {
      if (busy) return;
      items.forEach(item => URL.revokeObjectURL(item.url));
      items = [];
      batchId = createUploadId();
      showMessage('');
      setBusy(false);
      fileInput.click();
    });

    root.querySelectorAll('[data-guest-snap-legal-open]').forEach(button => {
      button.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        openLegalModal(button.getAttribute('data-guest-snap-legal-open') || 'privacy');
      });
    });

    document.querySelectorAll('[data-guestbook-legal]').forEach(modal => {
      if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
      }
      modal.querySelectorAll('[data-guestbook-legal-close]').forEach(el => {
        el.addEventListener('click', () => closeLegalModal(modal));
      });
    });

    if (viewer) {
      viewer.querySelectorAll('[data-guest-snap-viewer-close]').forEach(el => {
        el.addEventListener('click', closeViewer);
      });
    }

    document.addEventListener('keydown', event => {
      if (!viewer || viewer.hidden) return;
      if (event.key === 'Escape') closeViewer();
    });

    renderPreview();

    window.addEventListener('beforeunload', event => {
      if (busy) {
        event.preventDefault();
        event.returnValue = '';
      }
    });
  });
})();
