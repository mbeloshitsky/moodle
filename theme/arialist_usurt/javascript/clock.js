function addNulls (val) {
	return (val < 10 ? '0' : '') + val
}

function displayTime (now) {
	var weekDays = ['Вскр', 'Пнд', 'Втр', 'Срд', 'Чтв', 'Птн', 'Сбт'];
	var secondsInYear = 86400 * 365.25;
	var secondsInWeek = 86400 * 7;
	var dWeek = Math.floor((now.getTime() % (86400*1000*7))/1000) - 86400;
	var currWeek = Math.floor((Math.floor(now.getTime()/1000) % secondsInYear + dWeek) / secondsInWeek);

	return now.getHours() + ':' + addNulls(now.getMinutes()) + ', ' 
		+ (weekDays[now.getDay()]) + ', ' 
		+ (currWeek % 2 == 0 ? '<span class="even">Четная</span>' : '<span class="odd">Нечетная</span>') + ', ' 
		+ now.getDate() + '.' + addNulls(now.getMonth()+1) + '.' + now.getFullYear()+'г.';
}


function displayCurrentTime () {
	var timeWidget = document.getElementById('clock');
	timeWidget.innerHTML = displayTime(new Date());
}

setInterval(displayCurrentTime, 10000);

document.addEventListener('DOMContentLoaded', displayCurrentTime);
