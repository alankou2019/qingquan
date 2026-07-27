(function () {
	var form = document.getElementById('commission_estimate_form');
	if (!form || !form.getAttribute('data-calculate-url')) {
		return;
	}

	var status = document.getElementById('commission_estimate_status');
	var saveRuleUrl = form.getAttribute('data-save-rule-url');
	var timer = null;
	var requestNo = 0;
	var activeRequest = null;
	var lastPayload = '';
	var ruleSaving = false;
	var levels = ['low', 'mid', 'high'];

	function setStatus(message) {
		if (status) {
			status.textContent = message;
		}
	}

	function updateText(id, value) {
		var element = document.getElementById(id);
		if (element) {
			element.textContent = value;
		}
	}

	function updateEstimate(estimate) {
		var i;
		var level;
		var row;
		for (i = 0; i < estimate.rows.length; i++) {
			row = estimate.rows[i];
			for (var j = 0; j < levels.length; j++) {
				level = levels[j];
				updateText('commission_estimate_row_' + row.project_id + '_' + level, row[level + '_amount']);
			}
		}
		for (i = 0; i < levels.length; i++) {
			level = levels[i];
			updateText('commission_estimate_total_' + level, estimate.commission[level]);
			updateText('commission_estimate_income_' + level, estimate.income[level]);
			updateText('commission_estimate_annual_' + level, estimate.annual[level]);
			var bar = document.getElementById('commission_estimate_bar_' + level);
			if (bar) {
				bar.style.width = estimate.bar_width[level] + '%';
			}
		}
	}

	function buildPostData() {
		var inputs = form.getElementsByTagName('input');
		var data = [];
		var i;
		for (i = 0; i < inputs.length; i++) {
			if (inputs[i].name && inputs[i].type !== 'submit') {
				data.push(encodeURIComponent(inputs[i].name) + '=' + encodeURIComponent(inputs[i].value));
			}
		}
		data.push('salary_ajax=1');
		return data.join('&');
	}

	function calculate() {
		if (ruleSaving) {
			return;
		}
		var payload = buildPostData();
		if (payload === lastPayload) {
			return;
		}
		lastPayload = payload;
		if (activeRequest && activeRequest.readyState !== 4) {
			activeRequest.abort();
		}
		var currentRequest = ++requestNo;
		var xhr = activeRequest = new XMLHttpRequest();
		setStatus('正在计算...');
		xhr.open('POST', form.getAttribute('data-calculate-url'), true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.onreadystatechange = function () {
			if (xhr.readyState !== 4 || currentRequest !== requestNo) {
				return;
			}
			if (xhr.status >= 200 && xhr.status < 300) {
				try {
					var result = JSON.parse(xhr.responseText);
					if (result.status === 'y' && result.data) {
						updateEstimate(result.data);
						setStatus('已按当前规则计算');
						return;
					}
					lastPayload = '';
					setStatus(result.error || '计算失败');
				} catch (error) {
					lastPayload = '';
					setStatus('计算结果读取失败');
				}
			} else {
				lastPayload = '';
				setStatus('计算失败，请稍后重试');
			}
		};
		xhr.send(payload);
	}

	function scheduleCalculate() {
		if (ruleSaving) {
			return;
		}
		if (timer) {
			window.clearTimeout(timer);
		}
		timer = window.setTimeout(calculate, 350);
	}

	var fields = form.getElementsByClassName ? form.getElementsByClassName('commission_estimate_input') : [];
	for (var i = 0; i < fields.length; i++) {
		fields[i].addEventListener('input', scheduleCalculate, false);
		fields[i].addEventListener('change', scheduleCalculate, false);
	}

	function getRuleFields(editor) {
		return editor.querySelectorAll ? editor.querySelectorAll('[data-rule-field]') : [];
	}

	function rememberRuleValues(editor) {
		var fields = getRuleFields(editor);
		for (var i = 0; i < fields.length; i++) {
			fields[i].setAttribute('data-original-value', fields[i].value);
		}
	}

	function restoreRuleValues(editor) {
		var fields = getRuleFields(editor);
		for (var i = 0; i < fields.length; i++) {
			var value = fields[i].getAttribute('data-original-value');
			if (value !== null) {
				fields[i].value = value;
			}
		}
	}

	function closeRuleEditor(editor, restore) {
		if (!editor) {
			return;
		}
		if (restore) {
			restoreRuleValues(editor);
		}
		editor.className = 'estimate_rule_editor';
	}

	function findRuleEditor(element) {
		while (element && element !== form) {
			if ((' ' + element.className + ' ').indexOf(' estimate_rule_editor ') !== -1) {
				return element;
			}
			element = element.parentNode;
		}
		return null;
	}

	function buildRulePostData(editor, projectId) {
		var data = [buildPostData(), 'project_id=' + encodeURIComponent(projectId)];
		var fields = getRuleFields(editor);
		for (var i = 0; i < fields.length; i++) {
			data.push(encodeURIComponent(fields[i].getAttribute('data-rule-field')) + '=' + encodeURIComponent(fields[i].value));
		}
		return data.join('&');
	}

	var ruleEdits = form.getElementsByClassName ? form.getElementsByClassName('commission_rule_edit') : [];
	for (var editIndex = 0; editIndex < ruleEdits.length; editIndex++) {
		ruleEdits[editIndex].addEventListener('click', function () {
			var projectId = this.getAttribute('data-project-id');
			var editor = document.getElementById('commission_rule_editor_' + projectId);
			if (!editor) {
				return;
			}
			var editors = form.getElementsByClassName ? form.getElementsByClassName('estimate_rule_editor') : [];
			for (var i = 0; i < editors.length; i++) {
				if (editors[i] !== editor && (' ' + editors[i].className + ' ').indexOf(' is_open ') !== -1) {
					closeRuleEditor(editors[i], true);
				}
			}
			if ((' ' + editor.className + ' ').indexOf(' is_open ') !== -1) {
				closeRuleEditor(editor, true);
				return;
			}
			rememberRuleValues(editor);
			editor.className = 'estimate_rule_editor is_open';
		}, false);
	}

	var ruleCancels = form.getElementsByClassName ? form.getElementsByClassName('commission_rule_cancel') : [];
	for (var cancelIndex = 0; cancelIndex < ruleCancels.length; cancelIndex++) {
		ruleCancels[cancelIndex].addEventListener('click', function () {
			closeRuleEditor(findRuleEditor(this), true);
		}, false);
	}

	var ruleSaves = form.getElementsByClassName ? form.getElementsByClassName('commission_rule_save') : [];
	for (var saveIndex = 0; saveIndex < ruleSaves.length; saveIndex++) {
		ruleSaves[saveIndex].addEventListener('click', function () {
			var button = this;
			var editor = findRuleEditor(button);
			var projectId = editor ? editor.getAttribute('data-project-id') : '';
			if (!editor || !projectId || !saveRuleUrl || ruleSaving) {
				return;
			}
			if (!window.confirm('保存后会同步修改“提成项目设置”中的规则，并影响以后使用该项目的提成测算。确认保存吗？')) {
				return;
			}
			if (timer) {
				window.clearTimeout(timer);
				timer = null;
			}
			if (activeRequest && activeRequest.readyState !== 4) {
				activeRequest.abort();
			}
			requestNo++;
			ruleSaving = true;
			button.disabled = true;
			setStatus('正在保存规则并重新计算...');
			var xhr = new XMLHttpRequest();
			xhr.open('POST', saveRuleUrl, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.onreadystatechange = function () {
				if (xhr.readyState !== 4) {
					return;
				}
				ruleSaving = false;
				button.disabled = false;
				if (xhr.status >= 200 && xhr.status < 300) {
					try {
						var result = JSON.parse(xhr.responseText);
						if (result.status === 'y' && result.data && result.data.estimate && result.data.project) {
							updateEstimate(result.data.estimate);
							updateText('commission_estimate_rule_summary_' + projectId, result.data.project.rule_summary);
							rememberRuleValues(editor);
							closeRuleEditor(editor, false);
							lastPayload = buildPostData();
							setStatus('规则已保存，测算结果已更新');
							return;
						}
						setStatus(result.error || '规则保存失败');
					} catch (error) {
						setStatus('规则保存结果读取失败');
					}
				} else {
					setStatus('规则保存失败，请稍后重试');
				}
			};
			xhr.send(buildRulePostData(editor, projectId));
		}, false);
	}

	var toggles = form.getElementsByClassName ? form.getElementsByClassName('commission_project_toggle') : [];
	for (var j = 0; j < toggles.length; j++) {
		toggles[j].addEventListener('click', function () {
			var projectId = this.getAttribute('data-project-id');
			var enabledInput = document.getElementById('commission_estimate_enabled_' + projectId);
			var row = document.getElementById('commission_estimate_project_' + projectId);
			if (!enabledInput) {
				return;
			}
			var enabled = enabledInput.value !== '1';
			enabledInput.value = enabled ? '1' : '0';
			this.textContent = enabled ? '停用' : '启用';
			this.className = 'estimate_toggle commission_project_toggle' + (enabled ? '' : ' is_disabled');
			if (row) {
				row.className = enabled ? '' : 'commission_project_disabled';
			}
			scheduleCalculate();
		}, false);
	}
}());
