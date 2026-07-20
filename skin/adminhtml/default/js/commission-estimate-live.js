(function () {
	var form = document.getElementById('commission_estimate_form');
	if (!form || !form.getAttribute('data-calculate-url')) {
		return;
	}

	var status = document.getElementById('commission_estimate_status');
	var timer = null;
	var requestNo = 0;
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
		var currentRequest = ++requestNo;
		var xhr = new XMLHttpRequest();
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
					setStatus(result.error || '计算失败');
				} catch (error) {
					setStatus('计算结果读取失败');
				}
			} else {
				setStatus('计算失败，请稍后重试');
			}
		};
		xhr.send(buildPostData());
	}

	function scheduleCalculate() {
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
}());
