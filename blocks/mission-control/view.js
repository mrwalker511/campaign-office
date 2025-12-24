/**
 * Mission Control - Frontend JavaScript Logic
 * Handles countdown timer with comprehensive error handling and validation
 * 
 * @package CampaignPress
 * @since 2.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Mission Control dashboard
    const missionControl = document.querySelector('.cp-mission-control');
    
    // Early exit if mission control block not present
    if (!missionControl) {
        console.debug('Mission Control: Block not found');
        return;
    }

    // Get target date from data attribute
    const targetDateStr = missionControl.getAttribute('data-date');
    
    // Validate date is present and properly formatted
    if (!targetDateStr || targetDateStr === '') {
        console.debug('Mission Control: No election date configured');
        // Show error state (already handled by PHP)
        return;
    }

    let targetDate;
    try {
        targetDate = new Date(targetDateStr);
        
        // Validate date object
        if (!(targetDate instanceof Date) || isNaN(targetDate.getTime())) {
            throw new Error('Invalid date format');
        }
        
        // Check if date is in the past
        const now = new Date();
        if (targetDate <= now) {
            console.debug('Mission Control: Election date has passed');
            // Display "Election Day" message or hide countdown
            updateExpiredState();
            return;
        }
    } catch (error) {
        console.error('Mission Control: Error parsing election date:', error);
        return;
    }

    // Cache DOM elements for better performance
    const dayElement = missionControl.querySelector('[data-unit="days"]');
    const hourElement = missionControl.querySelector('[data-unit="hours"]');
    const minuteElement = missionControl.querySelector('[data-unit="mins"]');
    
    // Validate DOM elements exist
    if (!dayElement || !hourElement || !minuteElement) {
        console.error('Mission Control: Required DOM elements not found');
        return;
    }

    /**
     * Update countdown display with calculated time remaining
     * Includes validation to ensure elements are available
     */
    function updateCountdown() {
        try {
            const now = new Date().getTime();
            const timeRemaining = targetDate.getTime() - now;

            // Check if countdown has expired
            if (timeRemaining <= 0) {
                updateExpiredState();
                cleanup();
                return;
            }

            // Calculate time units with overflow protection
            const days = Math.max(0, Math.floor(timeRemaining / (1000 * 60 * 60 * 24)));
            const hours = Math.max(0, Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)));
            const minutes = Math.max(0, Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60)));

            // Update display with error handling for missing elements
            safeUpdateElement(dayElement, days.toString());
            safeUpdateElement(hourElement, hours.toString());
            safeUpdateElement(minuteElement, minutes.toString());

        } catch (error) {
            console.error('Mission Control: Error updating countdown:', error);
            cleanup();
        }
    }

    /**
     * Safely update DOM element text content
     * Prevents errors if element is removed or not accessible
     */
    function safeUpdateElement(element, text) {
        if (element && typeof element.innerText !== 'undefined') {
            element.innerText = text;
        }
    }

    /**
     * Update UI when countdown expires
     * Handles post-election display state
     */
    function updateExpiredState() {
        if (dayElement && hourElement && minuteElement) {
            safeUpdateElement(dayElement, '0');
            safeUpdateElement(hourElement, '0');
            safeUpdateElement(minuteElement, '0');
        }
        
        // Show election day message
        const countdownModule = missionControl.querySelector('.cp-mc-countdown');
        if (countdownModule) {
            const expiredMessage = document.createElement('div');
            expiredMessage.className = 'cp-mc-expired-message';
            expiredMessage.textContent = 'Election Day';
            expiredMessage.setAttribute('role', 'alert');
            
            // Remove any existing error messages
            const existingErrors = countdownModule.querySelector('.cp-mc-error');
            if (existingErrors) {
                existingErrors.remove();
            }
            
            countdownModule.appendChild(expiredMessage);
        }
    }

    /**
     * Clean up resources and stop interval
     */
    function cleanup() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }

    // Start countdown interval
    const intervalId = setInterval(updateCountdown, 1000);
    
    // Initial display update
    updateCountdown();
    
    // Clean up on page unload
    window.addEventListener('beforeunload', cleanup);
    
    // Clean up if element is removed from DOM (for SPA scenarios)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && !document.contains(missionControl)) {
                cleanup();
                observer.disconnect();
            }
        });
    });
    
    if (missionControl.parentNode) {
        observer.observe(missionControl.parentNode, { childList: true });
    }
});