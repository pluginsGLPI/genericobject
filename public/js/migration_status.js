/**
 * -------------------------------------------------------------------------
 * GenericObject plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GenericObject.
 *
 * GenericObject is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GenericObject is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GenericObject. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 */

/* global CFG_GLPI */

const glpiGenericobjectDebounceTimers = new WeakMap();

function glpiGenericobjectUpdateCounter(input) {
    clearTimeout(glpiGenericobjectDebounceTimers.get(input));
    glpiGenericobjectDebounceTimers.set(input, setTimeout(() => glpiGenericobjectFetchSystemName(input), 300));
}

function glpiGenericobjectFetchSystemName(input) {
    const maxLen = parseInt(input.getAttribute('maxlength'));
    const counterId = input.dataset.counterTarget;
    const warningId = input.dataset.warningTarget;
    const resultId = input.dataset.resultTarget;
    const counter = counterId ? document.getElementById(counterId) : null;
    const warning = warningId ? document.getElementById(warningId) : null;
    const result = resultId ? document.getElementById(resultId) : null;

    fetch(
        `${CFG_GLPI.root_doc}/plugins/genericobject/front/migration_status.php?action=get_system_name&name=${encodeURIComponent(input.value)}`,
    )
    .then(r => r.json())
    .then(data => {
        const currentLen = data.system_name.length;
        if (counter) {
            counter.textContent = currentLen + '/' + maxLen;
            counter.className = 'small';
            if (currentLen >= maxLen) {
                counter.classList.add('text-danger', 'fw-bold');
            } else {
                counter.classList.add('text-muted');
            }
        }

        if (warning) {
            if (currentLen >= maxLen) {
                warning.classList.remove('d-none');
            } else {
                warning.classList.add('d-none');
            }
        }

        if (result) {
            result.textContent = data.system_name;
        }
    })
    .catch((e) => console.error(e.message));
}

document.addEventListener('shown.bs.modal', function (event) {
    const input = event.target.querySelector('[data-counter-target]');
    if (input) {
        glpiGenericobjectUpdateCounter(input);
    }
});

function copyCode() {
    const codeBlock = document.getElementById('codeBlock');
    const textArea = document.createElement('textarea');
    textArea.value = codeBlock.textContent;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);

    const copyBtn = document.querySelector('.copy-btn');
    copyBtn.innerHTML = `<i class="ti ti-check"></i> ${copyBtn.dataset.genericobjectCopiedLabel} !`;
    copyBtn.classList.remove('btn-outline-secondary');
    copyBtn.classList.add('btn-success');

    setTimeout(() => {
        copyBtn.innerHTML = `<i class="ti ti-clipboard"></i> ${copyBtn.dataset.genericobjectCopyLabel}`;
        copyBtn.classList.remove('btn-success');
        copyBtn.classList.add('btn-outline-secondary');
    }, 2000);
}

document.addEventListener('input', (e) => {
    if (e.target.matches('[data-genericobject-update-counter]')) {
        glpiGenericobjectUpdateCounter(e.target);
    }
});

document.addEventListener('click', (e) => {
    if (e.target.closest('[data-genericobject-copy-code]') !== null) {
        copyCode();
    }
});
