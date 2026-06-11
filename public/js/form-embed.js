/**
 * FormEmbed - Standalone Form Embedding Script with Scheduler Support
 * Version: 2.0.0
 * 
 * Usage:
 * <script src="https://yourdomain.com/js/form-embed.js"></script>
 * <script>
 *   FormEmbed.render('uuid', { container: '#form-container', theme: 'light' });
 * </script>
 */

(function(window) {
  'use strict';

  const FormEmbed = {
    version: '2.0.0',
    
    // State constants
    STATES: {
      LOADING: 'loading',
      FORM: 'form',
      SCHEDULER: 'scheduler',
      SUBMITTING: 'submitting',
      SUCCESS: 'success',
      ERROR: 'error'
    },

    /**
     * Render a form by UUID
     * @param {string} uuid - Form UUID
     * @param {object} options - Configuration options
     */
    render: function(uuid, options = {}) {
      const config = {
        container: options.container || '#form-embed',
        theme: options.theme || 'light',
        apiBase: options.apiBase || window.location.origin,
        onSuccess: options.onSuccess || null,
        onError: options.onError || null,
      };

      const container = document.querySelector(config.container);
      if (!container) {
        console.error('FormEmbed: Container not found:', config.container);
        return;
      }

      // Initialize state for this form instance
      const state = {
        currentState: this.STATES.LOADING,
        form: null,
        formData: {},
        selectedDate: this.getTodayString(),
        selectedSlot: null,
        availableSlots: [],
        slotsLoading: false,
      };

      // Show loading state
      container.innerHTML = this.getLoadingHTML(config.theme);

      // Fetch form configuration
      fetch(`${config.apiBase}/api/forms/${uuid}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            state.form = data.form;
            state.currentState = this.STATES.FORM;
            this.renderCurrentState(container, state, config);
          } else {
            state.currentState = this.STATES.ERROR;
            container.innerHTML = this.getErrorHTML(data.message, config.theme);
          }
        })
        .catch(error => {
          console.error('FormEmbed: Failed to load form:', error);
          state.currentState = this.STATES.ERROR;
          container.innerHTML = this.getErrorHTML('Failed to load form. Please try again later.', config.theme);
        });
    },

    /**
     * Get today's date as YYYY-MM-DD string
     */
    getTodayString: function() {
      const today = new Date();
      return today.toISOString().split('T')[0];
    },

    /**
     * Render the current state
     */
    renderCurrentState: function(container, state, config) {
      const { form } = state;
      
      switch (state.currentState) {
        case this.STATES.FORM:
          this.renderFormStep(container, state, config);
          break;
        case this.STATES.SCHEDULER:
          this.renderSchedulerStep(container, state, config);
          break;
        case this.STATES.SUBMITTING:
          this.renderSubmittingState(container, state, config);
          break;
        case this.STATES.SUCCESS:
          this.renderSuccessState(container, state, config);
          break;
        default:
          break;
      }
    },

    /**
     * Render the form step (Step 1)
     */
    renderFormStep: function(container, state, config) {
      const { form } = state;
      const showSteps = form.scheduler?.enabled ?? false;
      
      const formHTML = `
        <div class="form-embed form-embed--${config.theme}">
          <style>${this.getCSS()}</style>
          
          ${showSteps ? this.getStepsIndicator(1) : ''}
          
          ${form.name ? `<h2 class="form-embed__title">${this.escapeHtml(form.name)}</h2>` : ''}
          ${form.description ? `<p class="form-embed__description">${this.escapeHtml(form.description)}</p>` : ''}
          
          <div class="form-embed__messages" id="form-messages"></div>
          
          <form class="form-embed__form" id="embed-form">
            ${form.fields.map((field, index) => this.renderField(field, index)).join('')}
            
            <button type="submit" class="form-embed__submit">
              ${showSteps ? 'Continue to Schedule' : 'Submit'}
            </button>
          </form>
        </div>
      `;

      container.innerHTML = formHTML;
      
      // Attach submit handler
      const formElement = container.querySelector('#embed-form');
      formElement.addEventListener('submit', (e) => {
        e.preventDefault();
        this.handleFormStepSubmit(container, formElement, state, config);
      });
    },

    /**
     * Handle form step submission
     */
    handleFormStepSubmit: function(container, formElement, state, config) {
      // Clear previous errors
      container.querySelectorAll('.form-embed__error').forEach(el => el.textContent = '');
      container.querySelectorAll('.form-embed__input--error').forEach(el => el.classList.remove('form-embed__input--error'));

      // Validate fields
      const validationErrors = this.validateFields(container, formElement, state);
      if (validationErrors.length > 0) {
        validationErrors.forEach(error => {
          const errorEl = container.querySelector(`#error-${error.field}`);
          const inputEl = container.querySelector(`[name="${error.field}"]`);
          if (errorEl) errorEl.textContent = error.message;
          if (inputEl) inputEl.classList.add('form-embed__input--error');
        });
        return;
      }

      // Collect form data
      const formData = new FormData(formElement);
      const fields = {};
      
      for (let [key, value] of formData.entries()) {
        if (key.endsWith('[]')) {
          const cleanKey = key.slice(0, -2);
          if (!fields[cleanKey]) fields[cleanKey] = [];
          fields[cleanKey].push(value);
        } else {
          fields[key] = value;
        }
      }
      
      state.formData = fields;

      // If scheduler is enabled, go to scheduler step
      if (state.form.scheduler?.enabled) {
        state.currentState = this.STATES.SCHEDULER;
        this.renderCurrentState(container, state, config);
        
        // Load slots or dates based on mode
        if (state.form.scheduler?.mode === 'day_only') {
          this.loadDates(container, state, config);
        } else {
          this.loadSlots(container, state, config);
        }
      } else {
        // Submit directly
        this.submitForm(container, state, config);
      }
    },

    /**
     * Render the scheduler step (Step 2)
     */
    renderSchedulerStep: function(container, state, config) {
      // Check scheduler mode
      if (state.form.scheduler?.mode === 'day_only') {
        this.renderDayOnlySchedulerStep(container, state, config);
        return;
      }
      
      const { form, selectedDate, availableSlots, slotsLoading, dailyCapReached } = state;
      
      const schedulerHTML = `
        <div class="form-embed form-embed--${config.theme}">
          <style>${this.getCSS()}</style>
          
          ${this.getStepsIndicator(2)}
          
          <h2 class="form-embed__title">Select a Time</h2>
          <p class="form-embed__description">Choose your preferred appointment time</p>
          
          <div class="form-embed__messages" id="form-messages"></div>
          
          <div class="form-embed__scheduler">
            <div class="form-embed__date-picker">
              <label class="form-embed__label" for="slot-date">Select Date</label>
              <input 
                type="date" 
                id="slot-date" 
                class="form-embed__input"
                value="${selectedDate}"
                min="${this.getTodayString()}"
              />
            </div>
            
            <div class="form-embed__slots" id="slots-container">
              ${slotsLoading ? this.getSlotsLoadingHTML() : this.renderSlots(availableSlots, state.selectedSlot)}
            </div>
            
            <div class="form-embed__actions">
              <button type="button" class="form-embed__back" id="back-btn">
                &larr; Back to Form
              </button>
              <button type="button" class="form-embed__submit" id="confirm-btn" ${!state.selectedSlot ? 'disabled' : ''}>
                Confirm Booking
              </button>
            </div>
          </div>
        </div>
      `;

      container.innerHTML = schedulerHTML;
      
      // Attach event handlers
      const dateInput = container.querySelector('#slot-date');
      dateInput.addEventListener('change', (e) => {
        state.selectedDate = e.target.value;
        state.selectedSlot = null;
        this.loadSlots(container, state, config);
      });

      const backBtn = container.querySelector('#back-btn');
      backBtn.addEventListener('click', () => {
        state.currentState = this.STATES.FORM;
        this.renderCurrentState(container, state, config);
      });

      const confirmBtn = container.querySelector('#confirm-btn');
      confirmBtn.addEventListener('click', () => {
        if (state.selectedSlot) {
          this.submitForm(container, state, config);
        }
      });

      // Attach slot click handlers
      this.attachSlotHandlers(container, state, config);
    },

    /**
     * Load available slots for selected date
     */
    loadSlots: function(container, state, config) {
      state.slotsLoading = true;
      const slotsContainer = container.querySelector('#slots-container');
      if (slotsContainer) {
        slotsContainer.innerHTML = this.getSlotsLoadingHTML();
      }

      fetch(`${config.apiBase}/api/forms/${state.form.uuid}/slots?date=${state.selectedDate}`)
        .then(response => response.json())
        .then(data => {
          state.slotsLoading = false;
          state.availableSlots = data.slots || [];
          state.dailyCapReached = data.daily_cap_reached || false;
          
          if (slotsContainer) {
            slotsContainer.innerHTML = this.renderSlots(state.availableSlots, state.selectedSlot, state.dailyCapReached);
            this.attachSlotHandlers(container, state, config);
          }
        })
        .catch(error => {
          console.error('FormEmbed: Failed to load slots:', error);
          state.slotsLoading = false;
          state.availableSlots = [];
          
          if (slotsContainer) {
            slotsContainer.innerHTML = '<p class="form-embed__no-slots">Failed to load available times. Please try again.</p>';
          }
        });
    },

    /**
     * Load available dates for day-only mode
     */
    loadDates: function(container, state, config) {
      state.datesLoading = true;
      const datesContainer = container.querySelector('#dates-container');
      if (datesContainer) {
        datesContainer.innerHTML = this.getSlotsLoadingHTML();
      }

      fetch(`${config.apiBase}/api/forms/${state.form.uuid}/dates?days=30`)
        .then(response => response.json())
        .then(data => {
          state.datesLoading = false;
          state.availableDates = data.dates || [];
          
          if (datesContainer) {
            datesContainer.innerHTML = this.renderDates(state.availableDates, state.selectedDate);
            this.attachDateHandlers(container, state, config);
          }
        })
        .catch(error => {
          console.error('FormEmbed: Failed to load dates:', error);
          state.datesLoading = false;
          state.availableDates = [];
          
          if (datesContainer) {
            datesContainer.innerHTML = '<p class="form-embed__no-slots">Failed to load available dates. Please try again.</p>';
          }
        });
    },

    /**
     * Render the day-only scheduler step (date calendar only)
     */
    renderDayOnlySchedulerStep: function(container, state, config) {
      const { form, availableDates, datesLoading } = state;
      
      const schedulerHTML = `
        <div class="form-embed form-embed--${config.theme}">
          <style>${this.getCSS()}</style>
          
          ${this.getStepsIndicator(2)}
          
          <h2 class="form-embed__title">Select a Date</h2>
          <p class="form-embed__description">Choose your preferred appointment date</p>
          
          <div class="form-embed__messages" id="form-messages"></div>
          
          <div class="form-embed__scheduler">
            <div class="form-embed__dates" id="dates-container">
              ${datesLoading ? this.getSlotsLoadingHTML() : this.renderDates(availableDates || [], state.selectedDate)}
            </div>
            
            <div class="form-embed__actions">
              <button type="button" class="form-embed__back" id="back-btn">
                &larr; Back to Form
              </button>
              <button type="button" class="form-embed__submit" id="confirm-btn" ${!state.selectedDate ? 'disabled' : ''}>
                Confirm Booking
              </button>
            </div>
          </div>
        </div>
      `;

      container.innerHTML = schedulerHTML;
      
      // Attach event handlers
      const backBtn = container.querySelector('#back-btn');
      backBtn.addEventListener('click', () => {
        state.currentState = this.STATES.FORM;
        this.renderCurrentState(container, state, config);
      });

      const confirmBtn = container.querySelector('#confirm-btn');
      confirmBtn.addEventListener('click', () => {
        if (state.selectedDate) {
          this.submitForm(container, state, config);
        }
      });

      // Attach date click handlers
      this.attachDateHandlers(container, state, config);
    },

    /**
     * Render available dates for day-only mode
     */
    renderDates: function(dates, selectedDate) {
      if (!dates || dates.length === 0) {
        return '<p class="form-embed__no-slots">No available dates. Please try again later.</p>';
      }

      const availableDates = dates.filter(d => d.available);
      if (availableDates.length === 0) {
        return '<p class="form-embed__no-slots">All dates are fully booked. Please try again later.</p>';
      }

      return `
        <div class="form-embed__dates-grid">
          ${dates.slice(0, 14).map(dateInfo => `
            <button 
              type="button"
              class="form-embed__date ${dateInfo.available ? '' : 'form-embed__date--unavailable'} ${selectedDate === dateInfo.date ? 'form-embed__date--selected' : ''}"
              data-date="${dateInfo.date}"
              ${dateInfo.available ? '' : 'disabled'}
              title="${!dateInfo.available ? (dateInfo.reason === 'closed' ? 'Closed' : 'Fully booked') : ''}"
            >
              <span class="form-embed__date-day">${dateInfo.day_name.slice(0, 3)}</span>
              <span class="form-embed__date-num">${new Date(dateInfo.date).getDate()}</span>
              ${!dateInfo.available ? `<span class="form-embed__date-status">${dateInfo.reason === 'closed' ? 'Closed' : 'Full'}</span>` : ''}
            </button>
          `).join('')}
        </div>
      `;
    },

    /**
     * Attach click handlers to date buttons
     */
    attachDateHandlers: function(container, state, config) {
      const dateButtons = container.querySelectorAll('.form-embed__date:not(.form-embed__date--unavailable)');
      const confirmBtn = container.querySelector('#confirm-btn');
      
      dateButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          // Remove previous selection
          container.querySelectorAll('.form-embed__date--selected').forEach(d => {
            d.classList.remove('form-embed__date--selected');
          });
          
          // Add selection to clicked date
          btn.classList.add('form-embed__date--selected');
          state.selectedDate = btn.dataset.date;
          state.selectedSlot = null; // Clear slot for day-only mode
          
          // Enable confirm button
          if (confirmBtn) {
            confirmBtn.disabled = false;
          }
        });
      });
    },

    /**
     * Render time slots
     */
    renderSlots: function(slots, selectedSlot, dailyCapReached) {
      // Check daily cap first
      if (dailyCapReached) {
        return '<p class="form-embed__no-slots form-embed__fully-booked">This day is fully booked. Please select another date.</p>';
      }
      
      if (!slots || slots.length === 0) {
        return '<p class="form-embed__no-slots">No available times for this date. Please select another date.</p>';
      }

      const availableSlots = slots.filter(s => s.available);
      if (availableSlots.length === 0) {
        return '<p class="form-embed__no-slots">All times are booked for this date. Please select another date.</p>';
      }

      return `
        <div class="form-embed__slots-grid">
          ${slots.map(slot => `
            <button 
              type="button"
              class="form-embed__slot ${slot.available ? '' : 'form-embed__slot--unavailable'} ${selectedSlot === slot.time ? 'form-embed__slot--selected' : ''}"
              data-time="${slot.time}"
              ${slot.available ? '' : 'disabled'}
            >
              ${slot.time}
            </button>
          `).join('')}
        </div>
      `;
    },

    /**
     * Attach click handlers to slot buttons
     */
    attachSlotHandlers: function(container, state, config) {
      const slotButtons = container.querySelectorAll('.form-embed__slot:not(.form-embed__slot--unavailable)');
      const confirmBtn = container.querySelector('#confirm-btn');
      
      slotButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          // Remove previous selection
          container.querySelectorAll('.form-embed__slot--selected').forEach(s => {
            s.classList.remove('form-embed__slot--selected');
          });
          
          // Add selection to clicked slot
          btn.classList.add('form-embed__slot--selected');
          state.selectedSlot = btn.dataset.time;
          
          // Enable confirm button
          if (confirmBtn) {
            confirmBtn.disabled = false;
          }
        });
      });
    },

    /**
     * Submit the form (with optional booking)
     */
    submitForm: function(container, state, config) {
      state.currentState = this.STATES.SUBMITTING;
      this.renderSubmittingState(container, state, config);

      const payload = {
        fields: state.formData,
      };

      // Add scheduled date/time if scheduler was used
      if (state.form.scheduler?.enabled && state.selectedSlot) {
        payload.scheduled_date = state.selectedDate;
        payload.scheduled_time = state.selectedSlot;
      } else if (state.form.scheduler?.enabled && state.form.scheduler?.mode === 'day_only' && state.selectedDate) {
        payload.scheduled_date = state.selectedDate;
      }

      fetch(`${config.apiBase}/api/forms/${state.form.uuid}/submit`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            state.currentState = this.STATES.SUCCESS;
            state.successMessage = data.message || 'Thank you! Your submission has been received.';
            if (state.form.scheduler?.enabled && state.selectedSlot) {
              state.successMessage = data.message || `Your appointment has been booked for ${state.selectedDate} at ${state.selectedSlot}.`;
            }
            this.renderCurrentState(container, state, config);
            
            if (config.onSuccess) {
              config.onSuccess(data);
            }
          } else {
            state.currentState = this.STATES.FORM;
            this.renderCurrentState(container, state, config);
            const messagesDiv = container.querySelector('#form-messages');
            if (messagesDiv) {
              messagesDiv.innerHTML = this.getErrorMessage(data.message || 'Something went wrong. Please try again.');
            }
            
            if (config.onError) {
              config.onError(data);
            }
          }
        })
        .catch(error => {
          console.error('FormEmbed: Submission error:', error);
          state.currentState = this.STATES.FORM;
          this.renderCurrentState(container, state, config);
          const messagesDiv = container.querySelector('#form-messages');
          if (messagesDiv) {
            messagesDiv.innerHTML = this.getErrorMessage('Failed to submit. Please try again.');
          }
          
          if (config.onError) {
            config.onError(error);
          }
        });
    },

    /**
     * Render submitting state
     */
    renderSubmittingState: function(container, state, config) {
      container.innerHTML = `
        <div class="form-embed form-embed--${config.theme}">
          <style>${this.getCSS()}</style>
          <div class="form-embed__loading">
            <div class="form-embed__spinner"></div>
            <p>Submitting...</p>
          </div>
        </div>
      `;
    },

    /**
     * Render success state
     */
    renderSuccessState: function(container, state, config) {
      container.innerHTML = `
        <div class="form-embed form-embed--${config.theme}">
          <style>${this.getCSS()}</style>
          <div class="form-embed__success">
            <svg class="form-embed__success-icon-large" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h2 class="form-embed__title">Success!</h2>
            <p class="form-embed__description">${this.escapeHtml(state.successMessage || 'Your submission has been received.')}</p>
          </div>
        </div>
      `;
    },

    /**
     * Get steps indicator HTML
     */
    getStepsIndicator: function(currentStep) {
      return `
        <div class="form-embed__steps">
          <div class="form-embed__step ${currentStep >= 1 ? 'form-embed__step--active' : ''}">
            <span class="form-embed__step-number">1</span>
            <span class="form-embed__step-label">Details</span>
          </div>
          <div class="form-embed__step-line ${currentStep >= 2 ? 'form-embed__step-line--active' : ''}"></div>
          <div class="form-embed__step ${currentStep >= 2 ? 'form-embed__step--active' : ''}">
            <span class="form-embed__step-number">2</span>
            <span class="form-embed__step-label">Schedule</span>
          </div>
        </div>
      `;
    },

    /**
     * Get slots loading HTML
     */
    getSlotsLoadingHTML: function() {
      return `
        <div class="form-embed__slots-loading">
          <div class="form-embed__spinner form-embed__spinner--small"></div>
          <p>Loading available times...</p>
        </div>
      `;
    },

    /**
     * Render a form field
     */
    renderField: function(field, index) {
      const required = field.required ? 'required' : '';
      const requiredMark = field.required ? '<span class="form-embed__required">*</span>' : '';
      
      let inputHTML = '';
      
      switch (field.type) {
        case 'textarea':
          inputHTML = `
            <textarea 
              id="field-${index}" 
              name="${field.name}" 
              placeholder="${this.escapeHtml(field.placeholder || '')}"
              ${required}
              class="form-embed__input form-embed__textarea"
              rows="4"
            ></textarea>
          `;
          break;
          
        case 'checkbox':
          inputHTML = `
            <div class="form-embed__checkbox-group">
              ${(field.options || []).map((option, optIndex) => `
                <label class="form-embed__checkbox-label">
                  <input 
                    type="checkbox" 
                    name="${field.name}[]" 
                    value="${this.escapeHtml(option)}"
                    class="form-embed__checkbox"
                  />
                  <span>${this.escapeHtml(option)}</span>
                </label>
              `).join('')}
            </div>
          `;
          break;
          
        case 'radio':
          inputHTML = `
            <div class="form-embed__radio-group">
              ${(field.options || []).map((option, optIndex) => `
                <label class="form-embed__radio-label">
                  <input 
                    type="radio" 
                    name="${field.name}" 
                    value="${this.escapeHtml(option)}"
                    ${required}
                    class="form-embed__radio"
                  />
                  <span>${this.escapeHtml(option)}</span>
                </label>
              `).join('')}
            </div>
          `;
          break;
          
        case 'select':
          inputHTML = `
            <select 
              id="field-${index}" 
              name="${field.name}" 
              ${required}
              class="form-embed__input form-embed__select"
            >
              <option value="">${this.escapeHtml(field.placeholder || 'Select an option')}</option>
              ${(field.options || []).map((option) => `
                <option value="${this.escapeHtml(option)}">${this.escapeHtml(option)}</option>
              `).join('')}
            </select>
          `;
          break;

        case 'phone':
          inputHTML = `
            <input 
              type="tel" 
              id="field-${index}" 
              name="${field.name}" 
              placeholder="${this.escapeHtml(field.placeholder || '07XXX XXXXXX')}"
              ${required}
              class="form-embed__input"
              data-validation="phone"
            />
            <p class="form-embed__hint">UK phone format: 07XXX XXXXXX or +44...</p>
          `;
          break;
          
        default:
          inputHTML = `
            <input 
              type="${field.type}" 
              id="field-${index}" 
              name="${field.name}" 
              placeholder="${this.escapeHtml(field.placeholder || '')}"
              ${required}
              class="form-embed__input"
            />
          `;
      }
      
      return `
        <div class="form-embed__field">
          <label class="form-embed__label" for="field-${index}">
            ${this.escapeHtml(field.label)}${requiredMark}
          </label>
          ${inputHTML}
          <div class="form-embed__error" id="error-${field.name}"></div>
        </div>
      `;
    },

    /**
     * Utility: Escape HTML
     */
    escapeHtml: function(text) {
      if (!text) return '';
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    },

    /**
     * Validate UK phone number
     * Accepts: 07XXX XXXXXX, +447XXX XXXXXX, 447XXX XXXXXX
     */
    isValidUKPhone: function(phone) {
      if (!phone) return false;
      // Remove spaces and dashes
      const cleaned = phone.replace(/[\s\-]/g, '');
      // UK mobile: 07XXX XXXXXX (11 digits starting with 07)
      // International: +447XXX XXXXXX or 447XXX XXXXXX
      const ukPhoneRegex = /^(?:(?:\+44|44|0)7\d{9})$/;
      return ukPhoneRegex.test(cleaned);
    },

    /**
     * Validate form fields
     */
    validateFields: function(container, formElement, state) {
      const errors = [];
      const formData = new FormData(formElement);
      
      state.form.fields.forEach(field => {
        let value;
        
        // Handle checkbox fields (array values)
        if (field.type === 'checkbox') {
          const checkboxValues = formData.getAll(field.name + '[]');
          value = checkboxValues.length > 0 ? checkboxValues : null;
        } else {
          value = formData.get(field.name) || '';
        }
        
        // Required field validation
        if (field.required) {
          if (field.type === 'checkbox') {
            if (!value || value.length === 0) {
              errors.push({
                field: field.name,
                message: `Please select at least one option for ${field.label}`
              });
            }
          } else if (!value || (typeof value === 'string' && !value.trim())) {
            errors.push({
              field: field.name,
              message: `${field.label} is required`
            });
          }
        }
        
        // Phone validation (format check)
        if (field.type === 'phone' && value && typeof value === 'string' && value.trim()) {
          if (!this.isValidUKPhone(value)) {
            errors.push({
              field: field.name,
              message: 'Please enter a valid UK phone number (e.g., 07XXX XXXXXX)'
            });
          }
        }
        
        // Email validation
        if (field.type === 'email' && value && typeof value === 'string' && value.trim()) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value)) {
            errors.push({
              field: field.name,
              message: 'Please enter a valid email address'
            });
          }
        }
      });
      
      return errors;
    },

    /**
     * Get loading HTML
     */
    getLoadingHTML: function(theme) {
      return `
        <div class="form-embed form-embed--${theme}">
          <style>${this.getCSS()}</style>
          <div class="form-embed__loading">
            <div class="form-embed__spinner"></div>
            <p>Loading form...</p>
          </div>
        </div>
      `;
    },

    /**
     * Get error HTML
     */
    getErrorHTML: function(message, theme) {
      return `
        <div class="form-embed form-embed--${theme}">
          <style>${this.getCSS()}</style>
          <div class="form-embed__error-box">
            <p>${this.escapeHtml(message)}</p>
          </div>
        </div>
      `;
    },

    /**
     * Get success message HTML (inline)
     */
    getSuccessMessage: function(message) {
      return `
        <div class="form-embed__success-message">
          <svg class="form-embed__success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p>${this.escapeHtml(message)}</p>
        </div>
      `;
    },

    /**
     * Get error message HTML (inline)
     */
    getErrorMessage: function(message) {
      return `
        <div class="form-embed__error-message">
          <svg class="form-embed__error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p>${this.escapeHtml(message)}</p>
        </div>
      `;
    },

    /**
     * Get CSS styles
     */
    getCSS: function() {
      return `
        .form-embed {
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
          max-width: 600px;
          margin: 0 auto;
          padding: 24px;
        }
        
        .form-embed--light {
          background: #ffffff;
          color: #1f2937;
          border: 1px solid #e5e7eb;
          border-radius: 8px;
        }
        
        .form-embed--dark {
          background: #1f2937;
          color: #f9fafb;
          border: 1px solid #374151;
          border-radius: 8px;
        }
        
        /* Steps indicator */
        .form-embed__steps {
          display: flex;
          align-items: center;
          justify-content: center;
          margin-bottom: 24px;
          gap: 8px;
        }
        
        .form-embed__step {
          display: flex;
          align-items: center;
          gap: 8px;
          opacity: 0.5;
        }
        
        .form-embed__step--active {
          opacity: 1;
        }
        
        .form-embed__step-number {
          width: 28px;
          height: 28px;
          border-radius: 50%;
          background: #e5e7eb;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 600;
          font-size: 14px;
        }
        
        .form-embed__step--active .form-embed__step-number {
          background: #3b82f6;
          color: white;
        }
        
        .form-embed__step-label {
          font-size: 14px;
          font-weight: 500;
        }
        
        .form-embed__step-line {
          width: 40px;
          height: 2px;
          background: #e5e7eb;
        }
        
        .form-embed__step-line--active {
          background: #3b82f6;
        }
        
        .form-embed__title {
          font-size: 24px;
          font-weight: 700;
          margin: 0 0 8px 0;
        }
        
        .form-embed__description {
          font-size: 14px;
          opacity: 0.7;
          margin: 0 0 24px 0;
        }
        
        .form-embed__field {
          margin-bottom: 20px;
        }
        
        .form-embed__label {
          display: block;
          font-size: 14px;
          font-weight: 500;
          margin-bottom: 6px;
        }
        
        .form-embed__required {
          color: #ef4444;
          margin-left: 2px;
        }
        
        .form-embed__input,
        .form-embed__textarea,
        .form-embed__select {
          width: 100%;
          padding: 10px 12px;
          font-size: 14px;
          border: 1px solid #d1d5db;
          border-radius: 6px;
          transition: border-color 0.2s;
          box-sizing: border-box;
        }

        .form-embed__select {
          appearance: none;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: right 10px center;
          background-size: 20px;
          padding-right: 40px;
          cursor: pointer;
        }
        
        .form-embed--light .form-embed__input,
        .form-embed--light .form-embed__textarea,
        .form-embed--light .form-embed__select {
          background-color: #ffffff;
          color: #1f2937;
        }
        
        .form-embed--dark .form-embed__input,
        .form-embed--dark .form-embed__textarea,
        .form-embed--dark .form-embed__select {
          background-color: #374151;
          color: #f9fafb;
          border-color: #4b5563;
        }
        
        .form-embed--dark .form-embed__select {
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        }
        
        .form-embed__input:focus,
        .form-embed__textarea:focus,
        .form-embed__select:focus {
          outline: none;
          border-color: #3b82f6;
        }

        .form-embed__input--error {
          border-color: #ef4444 !important;
        }

        .form-embed__error {
          color: #ef4444;
          font-size: 12px;
          margin-top: 4px;
          min-height: 16px;
        }

        .form-embed__hint {
          color: #6b7280;
          font-size: 12px;
          margin-top: 4px;
          margin-bottom: 0;
        }

        .form-embed--dark .form-embed__hint {
          color: #9ca3af;
        }
        
        .form-embed__checkbox-group,
        .form-embed__radio-group {
          display: flex;
          flex-direction: column;
          gap: 8px;
        }
        
        .form-embed__checkbox-label,
        .form-embed__radio-label {
          display: flex;
          align-items: center;
          gap: 8px;
          cursor: pointer;
          font-size: 14px;
        }
        
        .form-embed__checkbox,
        .form-embed__radio {
          width: 18px;
          height: 18px;
          cursor: pointer;
        }
        
        /* Scheduler styles */
        .form-embed__scheduler {
          display: flex;
          flex-direction: column;
          gap: 24px;
        }
        
        .form-embed__date-picker {
          display: flex;
          flex-direction: column;
          gap: 6px;
        }
        
        .form-embed__slots-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
          gap: 8px;
        }
        
        .form-embed__slot {
          padding: 12px 8px;
          font-size: 14px;
          font-weight: 500;
          border: 1px solid #d1d5db;
          border-radius: 6px;
          background: #ffffff;
          color: #1f2937;
          cursor: pointer;
          transition: all 0.2s;
        }
        
        .form-embed--dark .form-embed__slot {
          background: #374151;
          color: #f9fafb;
          border-color: #4b5563;
        }
        
        .form-embed__slot:hover:not(:disabled) {
          border-color: #3b82f6;
        }
        
        .form-embed__slot--selected {
          background: #3b82f6 !important;
          color: white !important;
          border-color: #3b82f6 !important;
        }
        
        .form-embed__slot--unavailable {
          opacity: 0.4;
          cursor: not-allowed;
          text-decoration: line-through;
        }
        
        .form-embed__no-slots {
          text-align: center;
          padding: 24px;
          color: #6b7280;
        }
        
        .form-embed__fully-booked {
          background: #fef3c7;
          color: #92400e;
          border: 1px solid #fcd34d;
          border-radius: 6px;
        }
        
        /* Day-only mode date grid */
        .form-embed__dates-grid {
          display: grid;
          grid-template-columns: repeat(7, 1fr);
          gap: 8px;
        }
        
        .form-embed__date {
          display: flex;
          flex-direction: column;
          align-items: center;
          padding: 12px 4px;
          font-size: 12px;
          border: 1px solid #d1d5db;
          border-radius: 6px;
          background: #ffffff;
          color: #1f2937;
          cursor: pointer;
          transition: all 0.2s;
        }
        
        .form-embed--dark .form-embed__date {
          background: #374151;
          color: #f9fafb;
          border-color: #4b5563;
        }
        
        .form-embed__date:hover:not(:disabled) {
          border-color: #3b82f6;
        }
        
        .form-embed__date--selected {
          background: #3b82f6 !important;
          color: white !important;
          border-color: #3b82f6 !important;
        }
        
        .form-embed__date--unavailable {
          opacity: 0.5;
          cursor: not-allowed;
        }
        
        .form-embed__date-day {
          font-weight: 500;
          text-transform: uppercase;
          font-size: 10px;
          opacity: 0.7;
        }
        
        .form-embed__date-num {
          font-size: 18px;
          font-weight: 600;
          margin: 4px 0;
        }
        
        .form-embed__date-status {
          font-size: 9px;
          color: #ef4444;
          text-transform: uppercase;
        }
        
        .form-embed__date--selected .form-embed__date-status {
          color: rgba(255, 255, 255, 0.8);
        }
        
        .form-embed__slots-loading {
          text-align: center;
          padding: 24px;
        }
        
        .form-embed__actions {
          display: flex;
          justify-content: space-between;
          gap: 12px;
        }
        
        .form-embed__back {
          padding: 12px 24px;
          font-size: 14px;
          font-weight: 500;
          color: #6b7280;
          background: transparent;
          border: 1px solid #d1d5db;
          border-radius: 6px;
          cursor: pointer;
          transition: all 0.2s;
        }
        
        .form-embed__back:hover {
          background: #f3f4f6;
        }
        
        .form-embed--dark .form-embed__back {
          color: #9ca3af;
          border-color: #4b5563;
        }
        
        .form-embed--dark .form-embed__back:hover {
          background: #374151;
        }

        .form-embed__submit {
          flex: 1;
          padding: 12px 24px;
          font-size: 16px;
          font-weight: 600;
          color: #ffffff;
          background: #3b82f6;
          border: none;
          border-radius: 6px;
          cursor: pointer;
          transition: background 0.2s;
        }
        
        .form-embed__submit:hover:not(:disabled) {
          background: #2563eb;
        }
        
        .form-embed__submit:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
        
        .form-embed__error {
          font-size: 13px;
          color: #ef4444;
          margin-top: 4px;
          min-height: 18px;
        }
        
        .form-embed__messages {
          margin-bottom: 20px;
        }
        
        .form-embed__success-message,
        .form-embed__error-message {
          display: flex;
          align-items: center;
          gap: 12px;
          padding: 12px 16px;
          border-radius: 6px;
          font-size: 14px;
        }
        
        .form-embed__success-message {
          background: #dcfce7;
          color: #166534;
          border: 1px solid #86efac;
        }
        
        .form-embed__error-message {
          background: #fee2e2;
          color: #991b1b;
          border: 1px solid #fca5a5;
        }
        
        .form-embed__success-icon,
        .form-embed__error-icon {
          width: 24px;
          height: 24px;
          flex-shrink: 0;
        }
        
        .form-embed__success {
          text-align: center;
          padding: 40px 20px;
        }
        
        .form-embed__success-icon-large {
          width: 64px;
          height: 64px;
          color: #22c55e;
          margin: 0 auto 16px;
        }
        
        .form-embed__loading {
          text-align: center;
          padding: 40px 20px;
        }
        
        .form-embed__spinner {
          width: 40px;
          height: 40px;
          border: 3px solid #e5e7eb;
          border-top-color: #3b82f6;
          border-radius: 50%;
          animation: form-embed-spin 0.8s linear infinite;
          margin: 0 auto 16px;
        }
        
        .form-embed__spinner--small {
          width: 24px;
          height: 24px;
          border-width: 2px;
          margin-bottom: 12px;
        }
        
        @keyframes form-embed-spin {
          to { transform: rotate(360deg); }
        }
        
        .form-embed__error-box {
          padding: 16px;
          background: #fee2e2;
          color: #991b1b;
          border: 1px solid #fca5a5;
          border-radius: 6px;
          text-align: center;
        }
        
        @media (max-width: 640px) {
          .form-embed {
            padding: 16px;
          }
          
          .form-embed__title {
            font-size: 20px;
          }
          
          .form-embed__slots-grid {
            grid-template-columns: repeat(3, 1fr);
          }
          
          .form-embed__dates-grid {
            grid-template-columns: repeat(4, 1fr);
          }
          
          .form-embed__actions {
            flex-direction: column;
          }
          
          .form-embed__back {
            order: 2;
          }
        }
      `;
    }
  };

  // Export to window
  window.FormEmbed = FormEmbed;

})(window);
