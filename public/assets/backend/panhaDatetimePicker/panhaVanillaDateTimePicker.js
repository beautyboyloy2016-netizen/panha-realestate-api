/**
 * Vanilla JavaScript PanhaDateTimePicker
 * A pure JavaScript date/time picker with no jQuery dependency
 * Converted from PanhaDateTimePicker
 */

class PanhaDateTimePicker {
  constructor(element, options = {}) {
    this.element = typeof element === 'string' ? document.querySelector(element) : element;

    if (!this.element) {
      console.error('PanhaDateTimePicker: Element not found');
      return;
    }

    // Default options
    const defaults = {
      mode: 'date', // 'date', 'datetime', 'time', 'range', 'multiple'
      theme: 'light', // 'light', 'dark', 'material', 'airbnb'
      inline: false,
      format: 'YYYY-MM-DD',
      timeFormat: 'HH:mm',
      minDate: null,
      maxDate: null,
      defaultDate: null,
      enableTime: false,
      weekStart: 0, // 0 = Sunday, 1 = Monday
      onChange: () => { },
      onOpen: () => { },
      onClose: () => { },
      showFooter: true,
      allowClear: true,
      // Flatpickr-inspired options
      enableSeconds: false,
      time_24hr: true,
      minuteIncrement: 1,
      hourIncrement: 1,
      disable: [],
      enable: [],
      noCalendar: false,
      disableMobile: false,
      showWeekNumbers: false,
      weekNumbers: false,
      altInput: false,
      altFormat: 'F j, Y',
      dateFormat: 'Y-m-d',
      defaultHour: 12,
      defaultMinute: 0,
      enableDates: [],
      disableDates: [],
      maxTime: null,
      minTime: null,
      position: 'auto',
      shorthandCurrentMonth: false,
      static: false,
      monthSelectorType: 'dropdown',
      showMonths: 1,
      conjunction: ', ',
      clickOpens: true,
      closeOnSelect: true,
      appendTo: null,
      animate: true,
      highlightToday: true,
      highlightWeekends: false,
      allowInput: true,
      preventDefaultOnEnter: true,
      utc: false,
      locale: 'en',
      firstDayOfWeek: 0,
      disableWeekends: false,
      onMonthChange: () => { },
      onYearChange: () => { },
      parseDate: null,
      formatDate: null,
      defaultDates: [],
      mobileInput: false,
      weekNumbersPosition: 'left'
    };

    this.settings = { ...defaults, ...options };
    this.months = ['January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'];
    this.weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // State
    this.picker = null;
    this.currentDate = new Date();
    this.selectedDate = null;
    this.selectedDates = [];
    this.rangeStart = null;
    this.rangeEnd = null;
    this.viewMonth = this.currentDate.getMonth();
    this.viewYear = this.currentDate.getFullYear();
    this.altInput = null;

    this.init();
  }

  init() {
    // Setup altInput if enabled
    if (this.settings.altInput && !this.settings.inline) {
      this.altInput = document.createElement('input');
      this.altInput.type = 'text';
      this.altInput.className = 'jq-alt-input';
      this.altInput.readOnly = true;
      this.element.parentNode.insertBefore(this.altInput, this.element.nextSibling);
      this.element.style.display = 'none';
    }

    if (this.settings.inline) {
      this.createInlinePicker();
    } else {
      if (this.settings.clickOpens) {
        const target = this.settings.altInput ? this.altInput : this.element;
        target.addEventListener('click', () => this.openPicker());
        target.addEventListener('focus', () => this.openPicker());
      }

      // Allow manual input if enabled
      if (this.settings.allowInput && !this.settings.altInput) {
        this.element.addEventListener('blur', () => {
          this.parseInputDate(this.element.value);
        });
      }

      // Close picker when clicking outside
      document.addEventListener('mousedown', (e) => {
        if (this.picker && this.picker.style.display !== 'none') {
          const target = this.settings.altInput ? this.altInput : this.element;
          if (!e.target.closest('.jq-datetimepicker') && e.target !== target) {
            this.closePicker();
          }
        }
      });

      // Keyboard navigation
      document.addEventListener('keydown', (e) => {
        if (this.picker && this.picker.style.display !== 'none') {
          if (e.key === 'Escape') {
            const activeSelector = this.picker.querySelector('.jq-dtp-selector.active');
            if (activeSelector) {
              this.hideSelectors();
            } else {
              this.closePicker();
            }
          }
        }
      });
    }
  }

  createPicker() {
    const modeClass = this.settings.mode === 'date' ? 'date-mode' :
      this.settings.mode === 'time' ? 'time-mode' :
        this.settings.mode === 'range' ? 'range-mode' : '';

    let pickerHtml;

    // Range mode uses dual-calendar layout
    if (this.settings.mode === 'range') {
      const nextMonth = (this.viewMonth + 1) % 12;
      const nextYear = this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear;

      pickerHtml = `
        <div class="jq-datetimepicker ${this.settings.theme} ${modeClass} ${this.settings.inline ? 'inline' : ''}">
          <div class="jq-dtp-range-input-display">
            <input type="text" class="jq-dtp-range-display" readonly placeholder="mm/dd/yyyy - mm/dd/yyyy">
          </div>
          <div class="jq-dtp-dual-calendar">
            <div class="jq-dtp-calendar-wrapper">
              <button class="jq-dtp-btn jq-dtp-prev-month-dual" type="button">‹</button>
              <div class="jq-dtp-calendar-single">
                <div class="jq-dtp-month-year-single">
                  <span class="jq-dtp-month-single jq-dtp-month-dropdown" data-calendar="left" data-type="month">${this.months[this.viewMonth]}</span>
                  <span class="jq-dtp-year-single jq-dtp-year-dropdown" data-calendar="left" data-type="year">${this.viewYear}</span>
                </div>
                <div class="jq-dtp-selector-container-left"></div>
                <div class="jq-dtp-calendar" data-month="${this.viewMonth}" data-year="${this.viewYear}"></div>
              </div>
            </div>
            <div class="jq-dtp-calendar-wrapper">
              <div class="jq-dtp-calendar-single">
                <div class="jq-dtp-month-year-single">
                  <span class="jq-dtp-month-single jq-dtp-month-dropdown" data-calendar="right" data-type="month">${this.months[nextMonth]}</span>
                  <span class="jq-dtp-year-single jq-dtp-year-dropdown" data-calendar="right" data-type="year">${nextYear}</span>
                </div>
                <div class="jq-dtp-selector-container-right"></div>
                <div class="jq-dtp-calendar jq-dtp-calendar-next" data-month="${nextMonth}" data-year="${nextYear}"></div>
              </div>
              <button class="jq-dtp-btn jq-dtp-next-month-dual" type="button">›</button>
            </div>
          </div>
          <div class="jq-dtp-range-footer">
            <div class="jq-dtp-range-display-text"></div>
            <div class="jq-dtp-range-actions">
              <button class="jq-dtp-cancel-btn" type="button">Cancel</button>
              <button class="jq-dtp-apply-range" type="button">Apply</button>
            </div>
          </div>
        </div>
      `;
    } else {
      pickerHtml = `
        <div class="jq-datetimepicker ${this.settings.theme} ${modeClass} ${this.settings.inline ? 'inline' : ''}">
          ${this.settings.mode !== 'time' ? `
          <div class="jq-dtp-header">
            <div class="jq-dtp-nav">
              <button class="jq-dtp-btn jq-dtp-prev-year" type="button">«</button>
              <button class="jq-dtp-btn jq-dtp-prev-month" type="button">‹</button>
            </div>
            <div class="jq-dtp-month-year">
              <span class="jq-dtp-month" data-type="month">${this.months[this.viewMonth]}</span>
              <span class="jq-dtp-year" data-type="year">${this.viewYear}</span>
            </div>
            <div class="jq-dtp-nav">
              <button class="jq-dtp-btn jq-dtp-next-month" type="button">›</button>
              <button class="jq-dtp-btn jq-dtp-next-year" type="button">»</button>
            </div>
          </div>
          <div class="jq-dtp-selector-container"></div>
          <div class="jq-dtp-calendar"></div>
          ` : ''}
          ${this.settings.mode === 'datetime' || this.settings.mode === 'time' ? this.createTimePickerHtml() : ''}
          ${this.settings.showFooter ? this.createFooterHtml() : ''}
        </div>
      `;
    }

    const temp = document.createElement('div');
    temp.innerHTML = pickerHtml.trim();
    this.picker = temp.firstChild;

    if (!this.settings.inline) {
      this.picker.style.position = 'absolute';
      this.picker.style.display = 'none';
      document.body.appendChild(this.picker);
    }

    if (this.settings.mode !== 'time') {
      this.renderCalendar();
    }

    // Use setTimeout to ensure DOM is fully ready before attaching events
    setTimeout(() => {
      this.attachEvents();
    }, 0);

    return this.picker;
  }

  createTimePickerHtml() {
    const hour = this.selectedDate ? this.selectedDate.getHours() : this.settings.defaultHour;
    const minute = this.selectedDate ? this.selectedDate.getMinutes() : this.settings.defaultMinute;
    const second = this.selectedDate ? this.selectedDate.getSeconds() : 0;
    const period = hour >= 12 ? 'PM' : 'AM';

    if (this.settings.mode === 'time') {
      const secondsDisplay = this.settings.enableSeconds ?
        `<span class="jq-dtp-time-separator">:</span><span class="jq-dtp-display-second">${second.toString().padStart(2, '0')}</span>` : '';

      return `
        <div class="jq-dtp-time">
          <div class="jq-dtp-time-container">
            <div class="jq-dtp-clock-display">
              <span class="jq-dtp-display-hour">${hour.toString().padStart(2, '0')}</span>
              <span class="jq-dtp-time-separator">:</span>
              <span class="jq-dtp-display-minute">${minute.toString().padStart(2, '0')}</span>
              ${secondsDisplay}
            </div>

            <div class="jq-dtp-time-sliders">
              <div class="jq-dtp-time-slider">
                <span class="jq-dtp-time-label">Hour</span>
                <input type="range" class="jq-dtp-hour-slider" min="0" max="23" step="${this.settings.hourIncrement}" value="${hour}">
                <span class="jq-dtp-time-value jq-dtp-hour-value">${hour}</span>
              </div>
              <div class="jq-dtp-time-slider">
                <span class="jq-dtp-time-label">Minute</span>
                <input type="range" class="jq-dtp-minute-slider" min="0" max="59" step="${this.settings.minuteIncrement}" value="${minute}">
                <span class="jq-dtp-time-value jq-dtp-minute-value">${minute}</span>
              </div>
              ${this.settings.enableSeconds ? `
              <div class="jq-dtp-time-slider">
                <span class="jq-dtp-time-label">Second</span>
                <input type="range" class="jq-dtp-second-slider" min="0" max="59" value="${second}">
                <span class="jq-dtp-time-value jq-dtp-second-value">${second}</span>
              </div>` : ''}
            </div>

            <div class="jq-dtp-time-input">
              <input type="number" class="jq-dtp-hour" min="0" max="23" step="${this.settings.hourIncrement}" value="${hour.toString().padStart(2, '0')}">
              <span class="jq-dtp-time-separator">:</span>
              <input type="number" class="jq-dtp-minute" min="0" max="59" step="${this.settings.minuteIncrement}" value="${minute.toString().padStart(2, '0')}">
              ${this.settings.enableSeconds ? `<span class="jq-dtp-time-separator">:</span>
              <input type="number" class="jq-dtp-second" min="0" max="59" value="${second.toString().padStart(2, '0')}">` : ''}
            </div>

            <div class="jq-dtp-period-toggle">
              <button class="jq-dtp-period-btn ${period === 'AM' ? 'active' : ''}" data-period="AM">AM</button>
              <button class="jq-dtp-period-btn ${period === 'PM' ? 'active' : ''}" data-period="PM">PM</button>
            </div>
          </div>
        </div>
      `;
    } else {
      return `
        <div class="jq-dtp-time">
          <div class="jq-dtp-time-input">
            <input type="number" class="jq-dtp-hour" min="0" max="23" value="${hour.toString().padStart(2, '0')}">
            <span class="jq-dtp-time-separator">:</span>
            <input type="number" class="jq-dtp-minute" min="0" max="59" value="${minute.toString().padStart(2, '0')}">
          </div>
        </div>
      `;
    }
  }

  createFooterHtml() {
    const leftButtons = [];
    const rightButtons = [];

    if (this.settings.allowClear) {
      leftButtons.push('<button class="jq-dtp-clear" type="button">Clear</button>');
    }

    if (this.settings.mode === 'datetime' || this.settings.mode === 'time') {
      rightButtons.push('<button class="jq-dtp-apply" type="button">Apply</button>');
    }

    rightButtons.push('<button class="jq-dtp-today-btn" type="button">Today</button>');

    return `
      <div class="jq-dtp-footer">
        <div>${leftButtons.join('')}</div>
        <div style="display: flex; gap: 8px;">${rightButtons.join('')}</div>
      </div>
    `;
  }

  renderCalendar() {
    // Range mode renders two calendars
    if (this.settings.mode === 'range') {
      this.renderDualCalendar();
      this.updateRangeDisplay();
      return;
    }

    const firstDay = new Date(this.viewYear, this.viewMonth, 1);
    const lastDay = new Date(this.viewYear, this.viewMonth + 1, 0);
    const prevLastDay = new Date(this.viewYear, this.viewMonth, 0);

    const startWeekDay = firstDay.getDay();
    const daysInMonth = lastDay.getDate();
    const daysInPrevMonth = prevLastDay.getDate();

    let html = '<div class="jq-dtp-weekdays">';

    // Add week number header if enabled
    if (this.settings.showWeekNumbers || this.settings.weekNumbers) {
      html += '<div class="jq-dtp-weekday jq-week-label">Wk</div>';
    }

    for (let i = 0; i < 7; i++) {
      const weekdayIndex = (i + this.settings.weekStart) % 7;
      html += `<div class="jq-dtp-weekday">${this.weekdays[weekdayIndex]}</div>`;
    }
    html += '</div><div class="jq-dtp-days">';

    // Helper function to get week number
    const getWeekNumber = (date) => {
      const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
      const dayNum = d.getUTCDay() || 7;
      d.setUTCDate(d.getUTCDate() + 4 - dayNum);
      const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
      return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
    };

    let cellsInCurrentRow = 0;
    const adjustedStartDay = (startWeekDay - this.settings.weekStart + 7) % 7;

    // Add week number for first row if enabled
    if (this.settings.showWeekNumbers || this.settings.weekNumbers) {
      const firstDayOfWeek = new Date(this.viewYear, this.viewMonth, 1 - adjustedStartDay);
      html += `<div class="jq-dtp-week-number">${getWeekNumber(firstDayOfWeek)}</div>`;
    }

    // Previous month days
    for (let i = adjustedStartDay - 1; i >= 0; i--) {
      const day = daysInPrevMonth - i;
      html += `<div class="jq-dtp-day other-month" data-date="${this.viewYear}-${this.viewMonth}-${day}">${day}</div>`;
      cellsInCurrentRow++;
    }

    // Current month days
    for (let day = 1; day <= daysInMonth; day++) {
      if (cellsInCurrentRow === 7 && (this.settings.showWeekNumbers || this.settings.weekNumbers)) {
        const weekDate = new Date(this.viewYear, this.viewMonth, day);
        html += `<div class="jq-dtp-week-number">${getWeekNumber(weekDate)}</div>`;
        cellsInCurrentRow = 0;
      }

      const date = new Date(this.viewYear, this.viewMonth, day);
      const dateStr = this.formatDate(date);
      const dayOfWeek = date.getDay();
      let classes = ['jq-dtp-day'];

      if (this.settings.highlightToday && this.isToday(date)) classes.push('today');
      if (this.isSelected(date)) classes.push('selected');
      if (this.isInRange(date)) classes.push('in-range');
      if (this.isDisabled(date)) classes.push('disabled');
      if (this.settings.highlightWeekends && (dayOfWeek === 0 || dayOfWeek === 6)) {
        classes.push('weekend');
      }

      html += `<div class="${classes.join(' ')}" data-date="${dateStr}">${day}</div>`;
      cellsInCurrentRow++;
    }

    // Next month days
    const totalCells = 42;
    const cellsFilled = adjustedStartDay + daysInMonth;
    const remainingCells = totalCells - cellsFilled;

    for (let day = 1; day <= remainingCells; day++) {
      html += `<div class="jq-dtp-day other-month" data-date="${this.viewYear}-${this.viewMonth + 2}-${day}">${day}</div>`;
    }

    html += '</div>';

    const calendar = this.picker.querySelector('.jq-dtp-calendar');
    if (calendar) calendar.innerHTML = html;
  }

  renderDualCalendar() {
    // Render first month
    const firstCalendar = this.picker.querySelector('.jq-dtp-calendar:not(.jq-dtp-calendar-next)');
    this.renderSingleMonth(this.viewYear, this.viewMonth, firstCalendar);

    // Render second month (next month)
    const nextMonth = (this.viewMonth + 1) % 12;
    const nextYear = this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear;
    const secondCalendar = this.picker.querySelector('.jq-dtp-calendar-next');
    this.renderSingleMonth(nextYear, nextMonth, secondCalendar);

    // Update month/year displays
    const monthSpans = this.picker.querySelectorAll('.jq-dtp-month-single');
    const yearSpans = this.picker.querySelectorAll('.jq-dtp-year-single');
    if (monthSpans[0]) monthSpans[0].textContent = this.months[this.viewMonth];
    if (yearSpans[0]) yearSpans[0].textContent = this.viewYear;
    if (monthSpans[1]) monthSpans[1].textContent = this.months[nextMonth];
    if (yearSpans[1]) yearSpans[1].textContent = nextYear;
  }

  renderSingleMonth(year, month, calendar) {
    if (!calendar) return;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const prevLastDay = new Date(year, month, 0);

    const startWeekDay = firstDay.getDay();
    const daysInMonth = lastDay.getDate();
    const daysInPrevMonth = prevLastDay.getDate();

    let html = '<div class="jq-dtp-weekdays">';
    for (let i = 0; i < 7; i++) {
      const weekdayIndex = (i + this.settings.weekStart) % 7;
      html += `<div class="jq-dtp-weekday">${this.weekdays[weekdayIndex]}</div>`;
    }
    html += '</div><div class="jq-dtp-days">';

    const adjustedStartDay = (startWeekDay - this.settings.weekStart + 7) % 7;

    // Previous month days
    for (let i = adjustedStartDay - 1; i >= 0; i--) {
      const day = daysInPrevMonth - i;
      html += `<div class="jq-dtp-day other-month">${day}</div>`;
    }

    // Current month days
    for (let day = 1; day <= daysInMonth; day++) {
      const date = new Date(year, month, day);
      const dateStr = this.formatDate(date);
      const dayOfWeek = date.getDay();
      let classes = ['jq-dtp-day'];

      if (this.settings.highlightToday && this.isToday(date)) classes.push('today');
      if (this.isSelected(date)) classes.push('selected');
      if (this.isInRange(date)) classes.push('in-range');
      if (this.isDisabled(date)) classes.push('disabled');
      if (this.settings.highlightWeekends && (dayOfWeek === 0 || dayOfWeek === 6)) {
        classes.push('weekend');
      }

      html += `<div class="${classes.join(' ')}" data-date="${dateStr}">${day}</div>`;
    }

    // Next month days
    const totalCells = 42;
    const cellsFilled = adjustedStartDay + daysInMonth;
    const remainingCells = totalCells - cellsFilled;

    for (let day = 1; day <= remainingCells; day++) {
      html += `<div class="jq-dtp-day other-month">${day}</div>`;
    }

    html += '</div>';
    calendar.innerHTML = html;
  }

  updateRangeDisplay() {
    const rangeInput = this.picker.querySelector('.jq-dtp-range-display');
    const rangeText = this.picker.querySelector('.jq-dtp-range-display-text');

    if (this.rangeStart && this.rangeEnd) {
      const formattedStart = this.formatDateShort(this.rangeStart);
      const formattedEnd = this.formatDateShort(this.rangeEnd);
      if (rangeInput) rangeInput.value = `${formattedStart} - ${formattedEnd}`;
      if (rangeText) rangeText.textContent = `${formattedStart} - ${formattedEnd}`;
    } else if (this.rangeStart) {
      const formattedStart = this.formatDateShort(this.rangeStart);
      if (rangeInput) rangeInput.value = formattedStart;
      if (rangeText) rangeText.textContent = '';
    } else {
      if (rangeInput) rangeInput.value = '';
      if (rangeText) rangeText.textContent = '';
    }
  }

  formatDateShort(date) {
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    const year = date.getFullYear();
    return `${month}/${day}/${year}`;
  }

  attachEvents() {
    // Navigation buttons
    const prevYear = this.picker.querySelector('.jq-dtp-prev-year');
    const prevMonth = this.picker.querySelector('.jq-dtp-prev-month');
    const nextMonth = this.picker.querySelector('.jq-dtp-next-month');
    const nextYear = this.picker.querySelector('.jq-dtp-next-year');

    if (prevYear) prevYear.addEventListener('click', (e) => { e.stopPropagation(); this.changeYear(-1); });
    if (prevMonth) prevMonth.addEventListener('click', (e) => { e.stopPropagation(); this.changeMonth(-1); });
    if (nextMonth) nextMonth.addEventListener('click', (e) => { e.stopPropagation(); this.changeMonth(1); });
    if (nextYear) nextYear.addEventListener('click', (e) => { e.stopPropagation(); this.changeYear(1); });

    // Day selection - delegate to parent (skip for range mode as it's handled separately)
    if (this.settings.mode !== 'range') {
      const calendar = this.picker.querySelector('.jq-dtp-calendar');
      if (calendar) {
        calendar.addEventListener('click', (e) => {
          if (e.target.classList.contains('jq-dtp-day') && !e.target.classList.contains('disabled')) {
            this.selectDate(e.target.dataset.date);
          }
        });
      }
    }

    // Footer buttons
    const todayBtn = this.picker.querySelector('.jq-dtp-today-btn');
    if (todayBtn) {
      todayBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const today = new Date();
        this.viewMonth = today.getMonth();
        this.viewYear = today.getFullYear();
        this.updateMonthYearDisplay();
        this.renderCalendar();
        this.selectDate(this.formatDate(today));
      });
    }

    const clearBtn = this.picker.querySelector('.jq-dtp-clear');
    if (clearBtn) {
      clearBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.clearSelection();
        if (!this.settings.inline) this.closePicker();
      });
    }

    const applyBtn = this.picker.querySelector('.jq-dtp-apply');
    if (applyBtn) {
      applyBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (!this.settings.inline) this.closePicker();
      });
    }

    // Time inputs
    if (this.settings.mode === 'datetime' || this.settings.mode === 'time') {
      const hourInput = this.picker.querySelector('.jq-dtp-hour');
      const minuteInput = this.picker.querySelector('.jq-dtp-minute');

      if (hourInput) hourInput.addEventListener('change', () => this.updateTime());
      if (minuteInput) minuteInput.addEventListener('change', () => this.updateTime());

      const timeSection = this.picker.querySelector('.jq-dtp-time');
      if (timeSection) {
        timeSection.addEventListener('click', (e) => e.stopPropagation());
      }

      // Enhanced time mode events
      if (this.settings.mode === 'time') {
        // Slider events
        const hourSlider = this.picker.querySelector('.jq-dtp-hour-slider');
        if (hourSlider) {
          hourSlider.addEventListener('input', (e) => {
            const hour = parseInt(e.target.value);
            const hourInput = this.picker.querySelector('.jq-dtp-hour');
            const hourValue = this.picker.querySelector('.jq-dtp-hour-value');
            const displayHour = this.picker.querySelector('.jq-dtp-display-hour');

            if (hourInput) hourInput.value = hour.toString().padStart(2, '0');
            if (hourValue) hourValue.textContent = hour;
            if (displayHour) displayHour.textContent = hour.toString().padStart(2, '0');
            this.updateTime();
          });
        }

        const minuteSlider = this.picker.querySelector('.jq-dtp-minute-slider');
        if (minuteSlider) {
          minuteSlider.addEventListener('input', (e) => {
            const minute = parseInt(e.target.value);
            const minuteInput = this.picker.querySelector('.jq-dtp-minute');
            const minuteValue = this.picker.querySelector('.jq-dtp-minute-value');
            const displayMinute = this.picker.querySelector('.jq-dtp-display-minute');

            if (minuteInput) minuteInput.value = minute.toString().padStart(2, '0');
            if (minuteValue) minuteValue.textContent = minute;
            if (displayMinute) displayMinute.textContent = minute.toString().padStart(2, '0');
            this.updateTime();
          });
        }

        const secondSlider = this.picker.querySelector('.jq-dtp-second-slider');
        if (secondSlider) {
          secondSlider.addEventListener('input', (e) => {
            const second = parseInt(e.target.value);
            const secondInput = this.picker.querySelector('.jq-dtp-second');
            const secondValue = this.picker.querySelector('.jq-dtp-second-value');
            const displaySecond = this.picker.querySelector('.jq-dtp-display-second');

            if (secondInput) secondInput.value = second.toString().padStart(2, '0');
            if (secondValue) secondValue.textContent = second;
            if (displaySecond) displaySecond.textContent = second.toString().padStart(2, '0');
            this.updateTime();
          });
        }

        // Sync inputs with sliders
        if (hourInput) {
          hourInput.addEventListener('change', () => {
            const hour = parseInt(hourInput.value) || 0;
            const hourSlider = this.picker.querySelector('.jq-dtp-hour-slider');
            const hourValue = this.picker.querySelector('.jq-dtp-hour-value');
            const displayHour = this.picker.querySelector('.jq-dtp-display-hour');

            if (hourSlider) hourSlider.value = hour;
            if (hourValue) hourValue.textContent = hour;
            if (displayHour) displayHour.textContent = hour.toString().padStart(2, '0');
          });
        }

        if (minuteInput) {
          minuteInput.addEventListener('change', () => {
            const minute = parseInt(minuteInput.value) || 0;
            const minuteSlider = this.picker.querySelector('.jq-dtp-minute-slider');
            const minuteValue = this.picker.querySelector('.jq-dtp-minute-value');
            const displayMinute = this.picker.querySelector('.jq-dtp-display-minute');

            if (minuteSlider) minuteSlider.value = minute;
            if (minuteValue) minuteValue.textContent = minute;
            if (displayMinute) displayMinute.textContent = minute.toString().padStart(2, '0');
          });
        }

        // Period toggle
        const periodBtns = this.picker.querySelectorAll('.jq-dtp-period-btn');
        periodBtns.forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const period = btn.dataset.period;
            periodBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const hourInput = this.picker.querySelector('.jq-dtp-hour');
            let hour = parseInt(hourInput.value);
            if (period === 'AM' && hour >= 12) {
              hour = hour === 12 ? 0 : hour - 12;
            } else if (period === 'PM' && hour < 12) {
              hour = hour === 0 ? 12 : hour + 12;
            }

            hourInput.value = hour.toString().padStart(2, '0');
            const hourSlider = this.picker.querySelector('.jq-dtp-hour-slider');
            const hourValue = this.picker.querySelector('.jq-dtp-hour-value');
            const displayHour = this.picker.querySelector('.jq-dtp-display-hour');

            if (hourSlider) hourSlider.value = hour;
            if (hourValue) hourValue.textContent = hour;
            if (displayHour) displayHour.textContent = hour.toString().padStart(2, '0');
            this.updateTime();
          });
        });
      }
    }

    // Month and Year selector events
    const monthSpan = this.picker.querySelector('.jq-dtp-month');
    const yearSpan = this.picker.querySelector('.jq-dtp-year');

    if (monthSpan) {
      monthSpan.addEventListener('click', (e) => {
        e.stopPropagation();
        e.preventDefault();
        this.showMonthSelector();
      });
    }

    if (yearSpan) {
      yearSpan.addEventListener('click', (e) => {
        e.stopPropagation();
        e.preventDefault();
        this.showYearSelector();
      });
    }

    // Close selector when clicking elsewhere in picker
    this.picker.addEventListener('click', (e) => {
      if (!e.target.closest('.jq-dtp-selector') &&
        !e.target.closest('.jq-dtp-month') &&
        !e.target.closest('.jq-dtp-year')) {
        this.hideSelectors();
      }
    });

    // Range mode specific events
    if (this.settings.mode === 'range') {
      console.log('Setting up range mode events...');

      // Navigation for dual calendar
      const prevMonthDual = this.picker.querySelector('.jq-dtp-prev-month-dual');
      const nextMonthDual = this.picker.querySelector('.jq-dtp-next-month-dual');

      console.log('Prev button:', prevMonthDual);
      console.log('Next button:', nextMonthDual);

      if (prevMonthDual) {
        prevMonthDual.addEventListener('click', (e) => {
          e.stopPropagation();
          this.changeMonth(-1);
        });
      }

      if (nextMonthDual) {
        nextMonthDual.addEventListener('click', (e) => {
          e.stopPropagation();
          this.changeMonth(1);
        });
      }

      // Day selection for both calendars
      const calendars = this.picker.querySelectorAll('.jq-dtp-calendar');
      console.log('Found calendars:', calendars.length);
      calendars.forEach(calendar => {
        calendar.addEventListener('click', (e) => {
          if (e.target.classList.contains('jq-dtp-day') &&
            !e.target.classList.contains('disabled') &&
            !e.target.classList.contains('other-month')) {
            this.selectDate(e.target.dataset.date);
          }
        });
      });

      // Apply button
      const applyRangeBtn = this.picker.querySelector('.jq-dtp-apply-range');
      console.log('Apply button found:', applyRangeBtn);

      if (applyRangeBtn) {
        console.log('Attaching click event to Apply button');
        applyRangeBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          console.log('Apply button clicked. Range:', this.rangeStart, 'to', this.rangeEnd);
          if (this.rangeStart && this.rangeEnd) {
            this.updateInput();
            this.updateRangeDisplay();
            console.log('Calling onChange callback...');
            this.settings.onChange.call(this.element, this.rangeStart, this.rangeEnd);
            if (!this.settings.inline) this.closePicker();
          } else {
            console.warn('Cannot apply: missing start or end date');
          }
        });
      } else {
        console.error('Apply button not found!');
      }

      // Cancel button
      const cancelBtn = this.picker.querySelector('.jq-dtp-cancel-btn');
      console.log('Cancel button found:', cancelBtn);

      if (cancelBtn) {
        cancelBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          this.rangeStart = null;
          this.rangeEnd = null;
          this.renderCalendar();
          this.updateRangeDisplay();
          if (!this.settings.inline) this.closePicker();
        });
      }

      // Month/Year dropdown selectors for range mode
      const monthDropdowns = this.picker.querySelectorAll('.jq-dtp-month-dropdown');
      const yearDropdowns = this.picker.querySelectorAll('.jq-dtp-year-dropdown');

      monthDropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', (e) => {
          e.stopPropagation();
          const calendar = dropdown.dataset.calendar;
          this.showRangeMonthSelector(calendar);
        });
      });

      yearDropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', (e) => {
          e.stopPropagation();
          const calendar = dropdown.dataset.calendar;
          this.showRangeYearSelector(calendar);
        });
      });
    }
  }

  showMonthSelector() {
    this.hideSelectors();
    const currentMonth = this.viewMonth;
    const today = new Date();

    let html = '<div class="jq-dtp-selector active">';
    html += `<div style="padding: 8px 8px 16px; text-align: center; font-weight: 600; color: #666;">Select Month - ${this.viewYear}</div>`;
    html += '<div class="jq-dtp-selector-grid months">';

    this.months.forEach((month, index) => {
      const classes = ['jq-dtp-selector-item'];
      if (index === currentMonth) classes.push('selected');
      if (index === today.getMonth() && this.viewYear === today.getFullYear()) {
        classes.push('current');
      }
      const shortName = month.length > 4 ? month.substr(0, 3) : month;
      html += `<div class="${classes.join(' ')}" data-month="${index}">${shortName}</div>`;
    });

    html += '</div></div>';

    const container = this.picker.querySelector('.jq-dtp-selector-container');
    container.innerHTML = html;

    // Attach month selector events
    const items = container.querySelectorAll('.jq-dtp-selector-item');
    items.forEach(item => {
      item.addEventListener('click', (e) => {
        e.stopPropagation();
        this.viewMonth = parseInt(item.dataset.month);
        this.updateMonthYearDisplay();
        this.renderCalendar();
        this.hideSelectors();
      });
    });
  }

  showYearSelector() {
    this.hideSelectors();
    const currentYear = this.viewYear;
    const startYear = Math.floor(currentYear / 10) * 10;
    const endYear = startYear + 19;

    let html = '<div class="jq-dtp-selector active">';
    html += '<div class="jq-dtp-year-nav">';
    html += `<button class="jq-dtp-year-prev" title="Previous 20 years">‹</button>`;
    html += `<span>${startYear} - ${endYear}</span>`;
    html += `<button class="jq-dtp-year-next" title="Next 20 years">›</button>`;
    html += '</div>';
    html += '<div class="jq-dtp-selector-grid years">';

    for (let year = startYear; year <= endYear; year++) {
      const classes = ['jq-dtp-selector-item'];
      if (year === currentYear) classes.push('selected');
      if (year === new Date().getFullYear()) classes.push('current');
      html += `<div class="${classes.join(' ')}" data-year="${year}">${year}</div>`;
    }

    html += '</div></div>';

    const container = this.picker.querySelector('.jq-dtp-selector-container');
    container.innerHTML = html;

    // Attach year selector events
    const items = container.querySelectorAll('.jq-dtp-selector-item');
    items.forEach(item => {
      item.addEventListener('click', (e) => {
        e.stopPropagation();
        this.viewYear = parseInt(item.dataset.year);
        this.updateMonthYearDisplay();
        this.renderCalendar();
        this.hideSelectors();
      });
    });

    // Navigation for year range
    const prevBtn = container.querySelector('.jq-dtp-year-prev');
    const nextBtn = container.querySelector('.jq-dtp-year-next');

    if (prevBtn) {
      prevBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.viewYear = startYear - 20;
        this.showYearSelector();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.viewYear = endYear + 1;
        this.showYearSelector();
      });
    }
  }

  hideSelectors() {
    const container = this.picker.querySelector('.jq-dtp-selector-container');
    if (container) container.innerHTML = '';

    // Also hide range mode selectors
    const containerLeft = this.picker.querySelector('.jq-dtp-selector-container-left');
    const containerRight = this.picker.querySelector('.jq-dtp-selector-container-right');
    if (containerLeft) containerLeft.innerHTML = '';
    if (containerRight) containerRight.innerHTML = '';

    const calendar = this.picker.querySelector('.jq-dtp-calendar');
    if (calendar) {
      calendar.classList.add('highlight');
      setTimeout(() => calendar.classList.remove('highlight'), 500);
    }
  }

  showRangeMonthSelector(calendarSide) {
    this.hideSelectors();
    const isLeft = calendarSide === 'left';
    const currentMonth = isLeft ? this.viewMonth : (this.viewMonth + 1) % 12;
    const currentYear = isLeft ? this.viewYear : (this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear);
    const today = new Date();

    let html = '<div class="jq-dtp-selector active">';
    html += `<div style="padding: 8px 8px 16px; text-align: center; font-weight: 600; color: #666;">Select Month - ${currentYear}</div>`;
    html += '<div class="jq-dtp-selector-grid months">';

    this.months.forEach((month, index) => {
      const classes = ['jq-dtp-selector-item'];
      if (index === currentMonth) classes.push('selected');
      if (index === today.getMonth() && currentYear === today.getFullYear()) {
        classes.push('current');
      }
      const shortName = month.length > 4 ? month.substr(0, 3) : month;
      html += `<div class="${classes.join(' ')}" data-month="${index}" data-side="${calendarSide}">${shortName}</div>`;
    });

    html += '</div></div>';

    const container = this.picker.querySelector(`.jq-dtp-selector-container-${calendarSide}`);
    if (container) {
      container.innerHTML = html;

      // Attach month selector events
      const items = container.querySelectorAll('.jq-dtp-selector-item');
      items.forEach(item => {
        item.addEventListener('click', (e) => {
          e.stopPropagation();
          const selectedMonth = parseInt(item.dataset.month);

          if (isLeft) {
            this.viewMonth = selectedMonth;
          } else {
            // For right calendar, adjust viewMonth
            const monthDiff = selectedMonth - currentMonth;
            this.viewMonth = (this.viewMonth + monthDiff + 12) % 12;
            if (selectedMonth < currentMonth) {
              this.viewYear++;
            }
          }

          this.renderCalendar();
          this.hideSelectors();
        });
      });
    }
  }

  showRangeYearSelector(calendarSide) {
    this.hideSelectors();
    const isLeft = calendarSide === 'left';
    const currentYear = isLeft ? this.viewYear : (this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear);
    const startYear = Math.floor(currentYear / 10) * 10;
    const endYear = startYear + 19;

    let html = '<div class="jq-dtp-selector active">';
    html += '<div class="jq-dtp-year-nav">';
    html += `<button class="jq-dtp-year-prev" title="Previous 20 years">‹</button>`;
    html += `<span>${startYear} - ${endYear}</span>`;
    html += `<button class="jq-dtp-year-next" title="Next 20 years">›</button>`;
    html += '</div>';
    html += '<div class="jq-dtp-selector-grid years">';

    for (let year = startYear; year <= endYear; year++) {
      const classes = ['jq-dtp-selector-item'];
      if (year === currentYear) classes.push('selected');
      if (year === new Date().getFullYear()) classes.push('current');
      html += `<div class="${classes.join(' ')}" data-year="${year}" data-side="${calendarSide}">${year}</div>`;
    }

    html += '</div></div>';

    const container = this.picker.querySelector(`.jq-dtp-selector-container-${calendarSide}`);
    if (container) {
      container.innerHTML = html;

      // Attach year selector events
      const items = container.querySelectorAll('.jq-dtp-selector-item');
      items.forEach(item => {
        item.addEventListener('click', (e) => {
          e.stopPropagation();
          const selectedYear = parseInt(item.dataset.year);

          if (isLeft) {
            this.viewYear = selectedYear;
          } else {
            // For right calendar, we need to adjust based on month
            const yearDiff = selectedYear - currentYear;
            this.viewYear += yearDiff;
          }

          this.renderCalendar();
          this.hideSelectors();
        });
      });

      // Navigation for year range
      const prevBtn = container.querySelector('.jq-dtp-year-prev');
      const nextBtn = container.querySelector('.jq-dtp-year-next');

      if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          const newYear = startYear - 20;
          // Temporarily adjust viewYear to show previous range
          if (isLeft) {
            this.viewYear = newYear;
          }
          this.showRangeYearSelector(calendarSide);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          const newYear = endYear + 1;
          // Temporarily adjust viewYear to show next range
          if (isLeft) {
            this.viewYear = newYear;
          }
          this.showRangeYearSelector(calendarSide);
        });
      }
    }
  }

  changeMonth(direction) {
    this.viewMonth += direction;
    if (this.viewMonth < 0) {
      this.viewMonth = 11;
      this.viewYear--;
    } else if (this.viewMonth > 11) {
      this.viewMonth = 0;
      this.viewYear++;
    }
    this.updateMonthYearDisplay();
    this.renderCalendar();
    this.settings.onMonthChange.call(this.element, this.viewMonth, this.viewYear);
  }

  changeYear(direction) {
    this.viewYear += direction;
    this.updateMonthYearDisplay();
    this.renderCalendar();
    this.settings.onYearChange.call(this.element, this.viewYear);
  }

  updateMonthYearDisplay() {
    const monthSpan = this.picker.querySelector('.jq-dtp-month');
    const yearSpan = this.picker.querySelector('.jq-dtp-year');

    if (monthSpan) monthSpan.textContent = this.months[this.viewMonth];
    if (yearSpan) yearSpan.textContent = this.viewYear;
  }

  selectDate(dateStr) {
    if (this.settings.mode === 'range') {
      console.log('selectDate called with:', dateStr);
      if (!this.rangeStart || (this.rangeStart && this.rangeEnd)) {
        // Starting new range selection
        this.rangeStart = new Date(dateStr);
        this.rangeEnd = null;
        console.log('Range start set:', this.rangeStart);
        this.updateRangeDisplay();
        this.renderCalendar();
      } else {
        // Completing the range
        this.rangeEnd = new Date(dateStr);
        if (this.rangeEnd < this.rangeStart) {
          [this.rangeStart, this.rangeEnd] = [this.rangeEnd, this.rangeStart];
        }
        console.log('Range complete:', this.rangeStart, 'to', this.rangeEnd);
        this.updateRangeDisplay();
        this.renderCalendar();
        // Don't call onChange or close here - wait for Apply button
      }
    } else if (this.settings.mode === 'multiple') {
      const newDate = new Date(dateStr);
      const existingIndex = this.selectedDates.findIndex(d =>
        d.toDateString() === newDate.toDateString()
      );

      if (existingIndex >= 0) {
        this.selectedDates.splice(existingIndex, 1);
      } else {
        this.selectedDates.push(newDate);
      }

      this.selectedDates.sort((a, b) => a - b);
      this.updateInput();
      this.settings.onChange.call(this.element, this.selectedDates);
      this.renderCalendar();
    } else {
      this.selectedDate = new Date(dateStr);
      this.updateInput();
      this.settings.onChange.call(this.element, this.selectedDate);
      this.renderCalendar();

      if (!this.settings.inline && this.settings.mode === 'date' && this.settings.closeOnSelect) {
        setTimeout(() => this.closePicker(), 150);
      }
    }
  }

  updateTime() {
    if (!this.selectedDate) this.selectedDate = new Date();

    const hourInput = this.picker.querySelector('.jq-dtp-hour');
    const minuteInput = this.picker.querySelector('.jq-dtp-minute');
    const secondInput = this.picker.querySelector('.jq-dtp-second');

    const hour = hourInput ? parseInt(hourInput.value) || 0 : 0;
    const minute = minuteInput ? parseInt(minuteInput.value) || 0 : 0;
    const second = this.settings.enableSeconds && secondInput ? (parseInt(secondInput.value) || 0) : 0;

    this.selectedDate.setHours(hour);
    this.selectedDate.setMinutes(minute);
    this.selectedDate.setSeconds(second);
    this.updateInput();
    this.settings.onChange.call(this.element, this.selectedDate);
  }

  updateInput() {
    let value = '';
    let altValue = '';

    if (this.settings.mode === 'range' && this.rangeStart && this.rangeEnd) {
      value = `${this.formatDate(this.rangeStart)} - ${this.formatDate(this.rangeEnd)}`;
      altValue = `${this.formatDateAlt(this.rangeStart)} - ${this.formatDateAlt(this.rangeEnd)}`;
    } else if (this.settings.mode === 'multiple' && this.selectedDates.length > 0) {
      value = this.selectedDates.map(d => this.formatDate(d)).join(this.settings.conjunction);
      altValue = this.selectedDates.map(d => this.formatDateAlt(d)).join(this.settings.conjunction);
    } else if (this.selectedDate) {
      value = this.formatDate(this.selectedDate);
      altValue = this.formatDateAlt(this.selectedDate);

      if (this.settings.mode === 'datetime') {
        value += ' ' + this.formatTime(this.selectedDate);
        altValue += ' ' + this.formatTime(this.selectedDate);
      } else if (this.settings.mode === 'time') {
        value = this.formatTime(this.selectedDate);
        altValue = this.formatTime(this.selectedDate);
      }
    }

    this.element.value = value;

    if (this.settings.altInput && this.altInput) {
      this.altInput.value = altValue || value;
    }
  }

  clearSelection() {
    this.selectedDate = null;
    this.selectedDates = [];
    this.rangeStart = null;
    this.rangeEnd = null;
    this.element.value = '';
    if (this.settings.altInput && this.altInput) {
      this.altInput.value = '';
    }
    this.renderCalendar();
    this.settings.onChange.call(this.element, null);
  }

  formatDate(date) {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  formatDateAlt(date) {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'];
    return `${monthNames[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
  }

  formatTime(date) {
    const hour = date.getHours().toString().padStart(2, '0');
    const minute = date.getMinutes().toString().padStart(2, '0');
    if (this.settings.enableSeconds) {
      const second = date.getSeconds().toString().padStart(2, '0');
      return `${hour}:${minute}:${second}`;
    }
    return `${hour}:${minute}`;
  }

  parseInputDate(value) {
    if (!value) return;
    try {
      const date = new Date(value);
      if (!isNaN(date.getTime())) {
        this.selectedDate = date;
        this.viewMonth = date.getMonth();
        this.viewYear = date.getFullYear();
        this.renderCalendar();
        this.settings.onChange.call(this.element, this.selectedDate);
      }
    } catch (e) {
      // Invalid date format
    }
  }

  isToday(date) {
    const today = new Date();
    return date.toDateString() === today.toDateString();
  }

  isSelected(date) {
    if (this.settings.mode === 'range') {
      return (this.rangeStart && date.toDateString() === this.rangeStart.toDateString()) ||
        (this.rangeEnd && date.toDateString() === this.rangeEnd.toDateString());
    } else if (this.settings.mode === 'multiple') {
      return this.selectedDates.some(d => d.toDateString() === date.toDateString());
    }
    return this.selectedDate && date.toDateString() === this.selectedDate.toDateString();
  }

  isInRange(date) {
    if (this.settings.mode === 'range' && this.rangeStart && this.rangeEnd) {
      return date >= this.rangeStart && date <= this.rangeEnd;
    }
    return false;
  }

  isDisabled(date) {
    const dateStr = this.formatDate(date);
    const dayOfWeek = date.getDay();

    // Check weekends if disabled
    if (this.settings.disableWeekends && (dayOfWeek === 0 || dayOfWeek === 6)) {
      return true;
    }

    // Check min/max dates
    if (this.settings.minDate && date < this.settings.minDate) return true;
    if (this.settings.maxDate && date > this.settings.maxDate) return true;

    // If enable array is set, only those dates are enabled
    if (this.settings.enable && this.settings.enable.length > 0) {
      return !this.settings.enable.some(d => {
        if (d instanceof Date) {
          return this.formatDate(d) === dateStr;
        } else if (typeof d === 'string') {
          return d === dateStr;
        } else if (typeof d === 'function') {
          return d(date);
        } else if (typeof d === 'object' && d.from && d.to) {
          const from = new Date(d.from);
          const to = new Date(d.to);
          return date >= from && date <= to;
        }
        return false;
      });
    }

    // Check disable array
    if (this.settings.disable && this.settings.disable.length > 0) {
      return this.settings.disable.some(d => {
        if (d instanceof Date) {
          return this.formatDate(d) === dateStr;
        } else if (typeof d === 'string') {
          return d === dateStr;
        } else if (typeof d === 'function') {
          return d(date);
        } else if (typeof d === 'object' && d.from && d.to) {
          const from = new Date(d.from);
          const to = new Date(d.to);
          return date >= from && date <= to;
        }
        return false;
      });
    }

    return false;
  }

  openPicker() {
    if (!this.picker) {
      this.createPicker();
    }

    const target = this.settings.altInput ? this.altInput : this.element;
    const rect = target.getBoundingClientRect();
    const inputHeight = target.offsetHeight;
    const pickerHeight = this.picker.offsetHeight;
    const windowHeight = window.innerHeight;
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    let top = rect.top + scrollTop + inputHeight + 8;
    let position = this.settings.position;

    // Auto positioning
    if (position === 'auto') {
      const spaceBelow = windowHeight + scrollTop - (rect.top + scrollTop + inputHeight);
      const spaceAbove = rect.top + scrollTop;

      if (spaceBelow < pickerHeight && spaceAbove > pickerHeight) {
        position = 'above';
      } else {
        position = 'below';
      }
    }

    if (position === 'above') {
      top = rect.top + scrollTop - pickerHeight - 8;
    }

    this.picker.style.top = top + 'px';
    this.picker.style.left = rect.left + 'px';
    this.picker.style.display = 'block';

    if (this.settings.animate) {
      this.picker.setAttribute('data-animate', 'true');
      this.picker.setAttribute('data-position', position);
    }

    this.settings.onOpen.call(this.element);
  }

  closePicker() {
    if (this.picker) {
      this.picker.style.display = 'none';
      this.hideSelectors();
      this.settings.onClose.call(this.element);
    }
  }

  createInlinePicker() {
    this.picker = this.createPicker();
    this.element.appendChild(this.picker);
  }

  destroy() {
    if (this.picker) {
      this.picker.remove();
      this.picker = null;
    }
    if (this.altInput) {
      this.altInput.remove();
      this.altInput = null;
    }
  }
}

// Global initialization function (optional - for backward compatibility)
if (typeof window !== 'undefined') {
  window.PanhaDateTimePicker = PanhaDateTimePicker;
}
