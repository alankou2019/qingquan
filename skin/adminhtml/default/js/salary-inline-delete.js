/* Keep salary list pages in place after a delete operation. */
function salaryInlineDelete(button, requestData, afterDelete) {
	if (!button || button.getAttribute('data-deleting') == '1') {
		return false;
	}
	var confirmText = button.getAttribute('data-delete-confirm') || '确认删除吗？';
	if (!window.confirm(confirmText)) {
		return false;
	}
	var url = button.getAttribute('data-delete-url');
	if (!url) {
		return false;
	}
	var data = requestData || {};
	data.salary_ajax = 1;
	var pairs = [];
	for (var key in data) {
		if (data.hasOwnProperty(key)) {
			pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
		}
	}
	var originalText = button.innerHTML;
	button.setAttribute('data-deleting', '1');
	button.disabled = true;
	button.innerHTML = '删除中...';
	var xhr = new XMLHttpRequest();
	xhr.open('POST', url, true);
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
	xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	xhr.onreadystatechange = function () {
		if (xhr.readyState != 4) {
			return;
		}
		button.removeAttribute('data-deleting');
		button.disabled = false;
		button.innerHTML = originalText;
		var result = null;
		try {
			result = JSON.parse(xhr.responseText);
		} catch (e) {
			window.alert('删除请求未得到有效响应，请刷新页面后重试。');
			return;
		}
		if (xhr.status < 200 || xhr.status >= 300 || !result || result.status != 'y') {
			window.alert(result && result.error ? result.error : '删除失败，请稍后重试。');
			return;
		}
		var rowId = button.getAttribute('data-delete-row-id');
		if (rowId) {
			var row = document.getElementById(rowId);
			if (row && row.parentNode) {
				row.parentNode.removeChild(row);
			}
		}
		var templateId = button.getAttribute('data-delete-template-id');
		if (templateId) {
			var items = document.querySelectorAll('[data-salary-template-id="' + templateId + '"]');
			for (var i = items.length - 1; i >= 0; i--) {
				if (items[i].parentNode) {
					items[i].parentNode.removeChild(items[i]);
				}
			}
		}
		if (typeof afterDelete == 'function') {
			afterDelete(result.data || {}, button);
		}
		salaryInlineDeleteMessage((result.data && result.data.message) ? result.data.message : '删除成功');
	};
	xhr.send(pairs.join('&'));
	return false;
}

function salaryInlineDeleteMessage(message) {
	var box = document.getElementById('salary_inline_delete_message');
	if (!box) {
		return;
	}
	box.innerHTML = message;
	box.style.display = 'block';
	window.setTimeout(function () {
		box.style.display = 'none';
	}, 2600);
}
