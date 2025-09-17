// Date validation for availability form
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    
    // Set min date for start and end date inputs
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput) {
        startDateInput.min = today;
    }
    
    if (endDateInput) {
        endDateInput.min = today;
    }
    
    // Update end date min when start date changes
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value;
            
            // If end date is before start date, reset it
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        });
    }
    
    // Calculate total price on booking page
    const calculateTotal = function() {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        const pricePerDay = parseFloat(document.getElementById('price_per_day').value);
        
        if (startDate && endDate && pricePerDay && startDate <= endDate) {
            const timeDiff = endDate.getTime() - startDate.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            const total = daysDiff * pricePerDay;
            
            document.getElementById('total_days').textContent = daysDiff;
            document.getElementById('total_price').textContent = 'Rs. ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        }
    };
    
    // Add event listeners for date changes on booking page
    const bookingStartDate = document.getElementById('start_date');
    const bookingEndDate = document.getElementById('end_date');
    
    if (bookingStartDate && bookingEndDate) {
        bookingStartDate.addEventListener('change', calculateTotal);
        bookingEndDate.addEventListener('change', calculateTotal);
    }
});