function addNulls (val) {
	return (val < 10 ? '0' : '') + val
}

function displayCurrentTime () {
	var weekDays = ['Вскр', 'Пнд', 'Втр', 'Срд', 'Чтв', 'Птн', 'Сбт'];
	var timeWidget = document.getElementById('clock');
	var now = new Date();
	var nowYear = new Date('1.1.'+now.getFullYear());
	var currWeek = Math.floor((Number(now) - Number(nowYear))/(7*86400*1000));

	timeWidget.innerHTML = now.getHours() + ':' + addNulls(now.getMinutes()) + ', ' 
		+ (weekDays[now.getDay()]) + ', ' 
		+ (currWeek % 2 == 0 ? '<span class="even">Четная</span>' : '<span class="odd">Нечетная</span>') + ', ' 
		+ now.getDate() + '.' + addNulls(now.getMonth()+1) + '.' + now.getFullYear()+'г.';
}
setInterval(displayCurrentTime, 10000);

document.addEventListener('DOMContentLoaded', displayCurrentTime);
