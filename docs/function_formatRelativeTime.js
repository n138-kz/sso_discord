function formatRelativeTime(date) {
	const now = new Date();
	const diffInSeconds = Math.floor((date - now) / 1000);

	// 時間の単位と秒数の定義
	const units = [
		{ label: 'year',   seconds: 31536000 },
		{ label: 'month',  seconds: 2592000 },
		{ label: 'day',    seconds: 86400 },
		{ label: 'hour',   seconds: 3600 },
		{ label: 'minute', seconds: 60 },
		{ label: 'second', seconds: 1 }
	];

	// 日本語設定で初期化
	const rtf = new Intl.RelativeTimeFormat('ja', { numeric: 'always' });

	for (const unit of units) {
		if (Math.abs(diffInSeconds) >= unit.seconds || unit.label === 'second') {
			const value = Math.floor(diffInSeconds / unit.seconds);
			return rtf.format(value, unit.label);
		}
	}
}
