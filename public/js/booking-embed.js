/**
 * InteTeam Booking Embed Script
 * 
 * Usage:
 * <script src="https://your-app.com/js/booking-embed.js" data-booking="UUID"></script>
 * 
 * Optional attributes:
 * - data-container="custom-id" - ID of container element (default: creates one)
 * - data-min-height="600" - Minimum iframe height in pixels
 */
(function() {
  'use strict';

  const script = document.currentScript;
  if (!script) {
    console.error('InteTeam Booking: Unable to locate script element');
    return;
  }

  const bookingUuid = script.dataset.booking;
  const containerId = script.dataset.container;
  const minHeight = parseInt(script.dataset.minHeight || '600', 10);

  if (!bookingUuid) {
    console.error('InteTeam Booking: Missing data-booking attribute');
    return;
  }

  // Get base URL from script src
  const scriptSrc = script.src;
  const baseUrl = scriptSrc.substring(0, scriptSrc.lastIndexOf('/js/booking-embed.js'));

  // Create or find container
  let container;
  if (containerId) {
    container = document.getElementById(containerId);
    if (!container) {
      console.error(`InteTeam Booking: Container #${containerId} not found`);
      return;
    }
  } else {
    container = document.createElement('div');
    container.id = 'inteteam-booking-' + bookingUuid.substring(0, 8);
    script.parentNode.insertBefore(container, script);
  }

  // Create iframe
  const iframe = document.createElement('iframe');
  iframe.src = `${baseUrl}/embed/booking/${bookingUuid}`;
  iframe.style.cssText = `
    width: 100%;
    min-height: ${minHeight}px;
    border: none;
    display: block;
  `;
  iframe.setAttribute('allow', 'payment');
  iframe.setAttribute('loading', 'lazy');
  iframe.setAttribute('title', 'Booking Form');

  // Add loading indicator
  const loader = document.createElement('div');
  loader.style.cssText = `
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: ${minHeight}px;
    color: #6B7280;
    font-family: system-ui, sans-serif;
  `;
  loader.textContent = 'Loading booking form...';
  container.appendChild(loader);

  iframe.onload = function() {
    loader.remove();
  };

  container.appendChild(iframe);

  // Handle messages from iframe
  window.addEventListener('message', function(event) {
    // Verify origin matches our base URL
    if (!event.origin.startsWith(baseUrl.split('/').slice(0, 3).join('/'))) {
      return;
    }

    const data = event.data;
    if (!data || typeof data !== 'object') return;

    switch (data.type) {
      case 'inteteam-booking-resize':
        if (data.height && typeof data.height === 'number') {
          iframe.style.height = Math.max(data.height, minHeight) + 'px';
        }
        break;

      case 'inteteam-booking-success':
        // Dispatch custom event for parent page to handle
        const successEvent = new CustomEvent('inteteam-booking-success', {
          detail: {
            bookingId: data.bookingId,
            uuid: bookingUuid,
          },
        });
        document.dispatchEvent(successEvent);

        // Optional: Redirect if data-redirect is set
        const redirectUrl = script.dataset.redirect;
        if (redirectUrl) {
          window.location.href = redirectUrl.replace('{bookingId}', data.bookingId || '');
        }
        break;
    }
  });

  // Expose API for programmatic control
  window.InteTeamBooking = window.InteTeamBooking || {};
  window.InteTeamBooking[bookingUuid] = {
    iframe: iframe,
    container: container,
    reload: function() {
      iframe.src = iframe.src;
    },
  };
})();
