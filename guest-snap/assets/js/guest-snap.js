(() => {
  'use strict';

  const UPLOAD_TIMEOUT_MS = 150_000;

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
    const selection = root.querySelector('[data-guest-snap-selection]');
    const anotherButton = root.querySelector('[data-guest-snap-another]');
    const maxFiles = Number(root.dataset.maxFiles) || 8;
    const maxBytes = Number(root.dataset.maxBytes) || 15 * 1024 * 1024;

    let items = [];
    let busy = false;
    let batchId = createUploadId();

    function showMessage(text, kind = 'info') {
      status.dataset.kind = kind;
      status.textContent = text;
      status.hidden = !text;
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
      progress.hidden = !value;
      renderPreview();
    }

    function renderPreview() {
      preview.replaceChildren();
      preview.hidden = !items.length;
      const totalBytes = items.reduce((sum, item) => sum + item.file.size, 0);
      selection.textContent = items.length
        ? `${items.length}/${maxFiles}장 선택 · ${(totalBytes / 1024 / 1024).toFixed(1)}MB`
        : '아직 선택한 사진이 없어요.';
      if (!busy) submitButton.disabled = root.dataset.canUpload !== '1' || !items.some(item => !item.done);

      items.forEach(item => {
        const thumbnail = document.createElement('li');
        thumbnail.className = 'guestSnapDrop__thumb';

        const image = document.createElement('img');
        image.src = item.url;
        image.alt = item.file.name;
        image.onerror = () => { image.hidden = true; };

        const label = document.createElement('span');
        label.className = 'guestSnapUploadLabel';
        label.textContent = `${item.file.name} · ${item.done ? '저장 완료' : item.error || item.phase || '대기'}`;
        thumbnail.dataset.state = item.done ? 'success' : item.error ? 'error' : 'pending';
        item.label = label;

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'guestSnapDrop__remove';
        removeButton.textContent = '×';
        removeButton.setAttribute('aria-label', `${item.file.name} 삭제`);
        removeButton.disabled = busy;
        removeButton.onclick = () => {
          URL.revokeObjectURL(item.url);
          items = items.filter(candidate => candidate !== item);
          renderPreview();
        };

        thumbnail.append(image, label, removeButton);
        preview.append(thumbnail);
      });
    }

    fileInput.addEventListener('change', () => {
      if (busy) return;

      if (items.length && items.every(item => item.done)) {
        items.forEach(item => URL.revokeObjectURL(item.url));
        items = [];
        batchId = createUploadId();
      }

      let warning = '';

      Array.from(fileInput.files).forEach(file => {
        if (items.length >= maxFiles) {
          warning = '한 번에 최대 8장까지 선택해주세요.';
          return;
        }

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
        });
      });

      fileInput.value = '';
      showMessage(warning, warning ? 'error' : 'info');
      submitButton.textContent = '사진 올리기';
      anotherButton.hidden = true;
      renderPreview();
    });

    function uploadPhoto(item, index, total) {
      item.error = '';
      item.phase = '전송 준비 중';
      renderPreview();
      showMessage(`${index + 1}/${total}장 · 전송 준비 중`);

      return new Promise(resolve => {
        // XHR exposes upload progress, including on mobile browsers.
        const request = new XMLHttpRequest();
        request.open('POST', form.action);
        request.timeout = UPLOAD_TIMEOUT_MS;

        const body = new FormData();
        body.append('photo', item.file);
        body.append('name', nameInput.value.trim());
        body.append('phone', phoneInput.value.trim());
        body.append('csrf', form.elements.csrf.value);
        body.append('batch', batchId);
        body.append('item', item.id);

        request.upload.onprogress = event => {
          if (!event.lengthComputable) return;

          progress.value = Math.round(event.loaded / event.total * 100);
          const saving = progress.value === 100 ? ' · 저장 확인 중' : '';
          item.phase = progress.value === 100 ? '저장 확인 중' : `${progress.value}% 전송`;
          item.label.textContent = `${item.file.name} · ${item.phase}`;
          showMessage(`${index + 1}/${total}장 · ${progress.value}% 전송${saving}`);
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

      showMessage(succeeded
        ? `${succeeded}장 성공, ${failed}장 실패. 실패한 사진만 다시 시도할 수 있어요.`
        : '사진을 저장하지 못했습니다. 사진별 안내를 확인하고 다시 시도해주세요.',
        'error'
      );
      submitButton.textContent = '실패한 사진 재시도';
    }

    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (busy || root.dataset.canUpload !== '1') return;

      if (!items.length) {
        showMessage('사진을 선택해주세요.');
        return;
      }

      const name = nameInput.value.trim();
      const phone = phoneInput.value.trim();

      if (!/^[\p{L}\p{M}\p{N} .’'_-]{1,20}$/u.test(name) || !/[\p{L}\p{N}]/u.test(name)) {
        showMessage('성함을 1~20자로 입력해주세요. 특수문자는 사용할 수 없습니다.', 'error');
        nameInput.focus();
        return;
      }

      if (phone.length > 30 || !/^\+?[0-9() -]+$/.test(phone) || !/^[0-9]{8,15}$/.test(phone.replace(/[^0-9]/g, ''))) {
        showMessage('전화번호는 숫자 8~15자리로 입력해주세요.', 'error');
        phoneInput.focus();
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

    renderPreview();

    window.addEventListener('beforeunload', event => {
      if (busy) {
        event.preventDefault();
        event.returnValue = '';
      }
    });
  });
})();
