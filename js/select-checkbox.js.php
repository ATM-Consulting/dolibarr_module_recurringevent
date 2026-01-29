<?php
/* Copyright (C) 2025 ATM Consulting
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */
?>
const initCheckboxSelector = () => {

	let elDateSelector = document.getElementById('ap');
	let elDateSection = elDateSelector.parentElement.parentElement;

	const findWeekDate = (date) => {
		date = date.split('/').reverse().join('-');

		const d = new Date(date);
		return d.getDay();
	};

	const checkDayBox = (findWeekDay) => {

		let days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

		days.map((day) => {
			let el = document.querySelector('#customCheck' + day);
			if (el) {
				el.removeAttribute('checked');
			}
		});

		let el = document.querySelector('#customCheck' + days[findWeekDay]);
		if (el) {
			el.setAttribute('checked', true);
		}
	};

	const handleDateSelectorChange = (event) => {
		console.log(ap.value)
		let weekDay = findWeekDate(ap.value);

		checkDayBox(weekDay);
	};

	const handleManualCheckboxChange = () => {
		elDateSelector.removeEventListener('input', handleDateSelectorChange);
		elDateSection.removeEventListener('click', handleDateSelectorChange);
	};

	const dynamicCheckboxesHandler = () => {

			elDateSelector.addEventListener('input', handleDateSelectorChange);
			elDateSection.addEventListener('click', handleDateSelectorChange);

			// Bind to jQuery datepicker change event
		   // $(elDateSelector).on('input', handleDateSelectorChange);
		  //  $(elDateSelector).on('input', handleDateSelectorChange);

	};

	document.addEventListener('DOMContentLoaded', dynamicCheckboxesHandler);
}

(() => initCheckboxSelector())()
