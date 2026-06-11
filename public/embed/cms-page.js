/**
 * InteTeam CRM - CMS Page Embed Widget
 * 
 * Usage:
 * <div id="privacy-policy"></div>
 * <script 
 *   src="https://your-crm.com/embed/cms-page.js"
 *   data-company="your-company-slug"
 *   data-page="privacy-policy"
 *   data-target="privacy-policy">
 * </script>
 * 
 * Attributes:
 * - data-company: Your company slug (required)
 * - data-page: The page slug to load (required)  
 * - data-target: ID of the container element (required)
 * - data-show-title: Show page title (default: true)
 * - data-show-updated: Show last updated date (default: true)
 * - data-loading-text: Custom loading text (default: "Loading...")
 * - data-error-text: Custom error text (default: "Failed to load content")
 */
(function() {
  'use strict';

  // Find the current script tag
  var currentScript = document.currentScript;
  if (!currentScript) {
    console.error('[CMS Embed] Could not find script tag');
    return;
  }

  // Get configuration from data attributes
  var config = {
    company: currentScript.getAttribute('data-company'),
    page: currentScript.getAttribute('data-page'),
    target: currentScript.getAttribute('data-target'),
    showTitle: currentScript.getAttribute('data-show-title') !== 'false',
    showUpdated: currentScript.getAttribute('data-show-updated') !== 'false',
    loadingText: currentScript.getAttribute('data-loading-text') || 'Loading...',
    errorText: currentScript.getAttribute('data-error-text') || 'Failed to load content. Please try again later.'
  };

  // Validate required attributes
  if (!config.company) {
    console.error('[CMS Embed] Missing required attribute: data-company');
    return;
  }
  if (!config.page) {
    console.error('[CMS Embed] Missing required attribute: data-page');
    return;
  }
  if (!config.target) {
    console.error('[CMS Embed] Missing required attribute: data-target');
    return;
  }

  // Get API base URL from script src
  var scriptSrc = currentScript.src;
  var baseUrl = scriptSrc.substring(0, scriptSrc.indexOf('/embed/'));
  var apiUrl = baseUrl + '/api/v1/' + config.company + '/pages/' + config.page;

  // Find target container
  var container = document.getElementById(config.target);
  if (!container) {
    console.error('[CMS Embed] Target element not found: #' + config.target);
    return;
  }

  // Add base styles
  var styles = document.createElement('style');
  styles.textContent = [
    '.cms-embed-loading { padding: 20px; text-align: center; color: #666; }',
    '.cms-embed-error { padding: 20px; text-align: center; color: #dc2626; background: #fef2f2; border-radius: 8px; }',
    '.cms-embed-content { line-height: 1.6; }',
    '.cms-embed-title { font-size: 1.5em; font-weight: bold; margin-bottom: 0.5em; }',
    '.cms-embed-updated { font-size: 0.875em; color: #666; margin-bottom: 1em; }',
    '.cms-embed-body h1 { font-size: 1.5em; font-weight: bold; margin: 1em 0 0.5em; }',
    '.cms-embed-body h2 { font-size: 1.25em; font-weight: bold; margin: 1em 0 0.5em; }',
    '.cms-embed-body h3 { font-size: 1.1em; font-weight: bold; margin: 1em 0 0.5em; }',
    '.cms-embed-body p { margin: 0.5em 0; }',
    '.cms-embed-body ul, .cms-embed-body ol { margin: 0.5em 0; padding-left: 1.5em; }',
    '.cms-embed-body a { color: #2563eb; text-decoration: underline; }'
  ].join('\n');
  document.head.appendChild(styles);

  // Show loading state
  container.innerHTML = '<div class="cms-embed-loading">' + config.loadingText + '</div>';

  // Fetch and render content
  fetch(apiUrl)
    .then(function(response) {
      if (!response.ok) {
        throw new Error('HTTP ' + response.status);
      }
      return response.json();
    })
    .then(function(json) {
      var data = json.data;
      var html = '<div class="cms-embed-content">';
      
      if (config.showTitle && data.title) {
        html += '<div class="cms-embed-title">' + escapeHtml(data.title) + '</div>';
      }
      
      if (config.showUpdated && data.updated_at) {
        var date = new Date(data.updated_at);
        html += '<div class="cms-embed-updated">Last updated: ' + date.toLocaleDateString() + '</div>';
      }
      
      html += '<div class="cms-embed-body">' + data.content + '</div>';
      html += '</div>';
      
      container.innerHTML = html;
    })
    .catch(function(error) {
      console.error('[CMS Embed] Error loading page:', error);
      container.innerHTML = '<div class="cms-embed-error">' + config.errorText + '</div>';
    });

  // Helper to escape HTML in title
  function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
})();
