(function () {
	var board = document.querySelector('.schedule-board');
	var today = board && board.querySelector('.schedule-day.is-today');

	if (!board || !today) {
		return;
	}

	if (window.matchMedia('(max-width: 1279px)').matches) {
		board.scrollLeft = today.offsetLeft - board.offsetLeft;
	}
})();
