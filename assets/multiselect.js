document.addEventListener('DOMContentLoaded', function() {
    // Initialize all multiselect components
    initializeMultiselects();
});

function initializeMultiselects() {
    const multiselectContainers = document.querySelectorAll('.lms-ui-multiselect-container');
    
    multiselectContainers.forEach(container => {
        const launcher = container.querySelector('.lms-ui-multiselect-launcher');
        const popup = container.querySelector('.lms-ui-multiselect-popup');
        const closeButton = container.querySelector('.close-button');
        const checkboxes = container.querySelectorAll('.lms-ui-multiselect-popup-list input[type="checkbox"]');
        const checkAllCheckbox = container.querySelector('.lms-ui-multiselect-popup-checkall input[type="checkbox"]');
        const selectElement = container.querySelector('select');
        
        // Toggle popup on launcher click
        launcher.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            togglePopup(popup);
        });
        
        // Close popup on close button click
        if (closeButton) {
            closeButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                hidePopup(popup);
            });
        }
        
        // Handle individual checkbox changes
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectElement(checkbox, selectElement);
                updateCheckAllState(checkboxes, checkAllCheckbox);
                updateListItemState(checkbox);
            });
        });
        
        // Handle check all checkbox
        if (checkAllCheckbox) {
            checkAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                checkboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    updateSelectElement(checkbox, selectElement);
                    updateListItemState(checkbox);
                });
            });
        }
        
        // Close popup when clicking outside
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                hidePopup(popup);
            }
        });
        
        // Handle keyboard navigation
        launcher.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePopup(popup);
            }
        });
        
        // Initialize states
        updateCheckAllState(checkboxes, checkAllCheckbox);
        checkboxes.forEach(checkbox => {
            updateListItemState(checkbox);
        });
    });
}

function togglePopup(popup) {
    if (popup.style.display === 'none' || !popup.style.display) {
        showPopup(popup);
    } else {
        hidePopup(popup);
    }
}

function showPopup(popup) {
    popup.style.display = 'block';
}

function hidePopup(popup) {
    popup.style.display = 'none';
}

function updateSelectElement(checkbox, selectElement) {
    const value = checkbox.value;
    const option = selectElement.querySelector(`option[value="${value}"]`);
    
    if (option) {
        if (checkbox.checked) {
            option.selected = true;
        } else {
            option.selected = false;
        }
    }
}

function updateCheckAllState(checkboxes, checkAllCheckbox) {
    if (!checkAllCheckbox) return;
    
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    const totalCount = checkboxes.length;
    
    if (checkedCount === 0) {
        checkAllCheckbox.checked = false;
        checkAllCheckbox.indeterminate = false;
    } else if (checkedCount === totalCount) {
        checkAllCheckbox.checked = true;
        checkAllCheckbox.indeterminate = false;
    } else {
        checkAllCheckbox.checked = false;
        checkAllCheckbox.indeterminate = true;
    }
}

function updateListItemState(checkbox) {
    const listItem = checkbox.closest('li');
    if (checkbox.checked) {
        listItem.classList.add('selected');
    } else {
        listItem.classList.remove('selected');
    }
} 