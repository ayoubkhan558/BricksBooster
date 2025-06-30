function initHtmlValidator() {
    const structurePanel = document.querySelector('#bricks-structure .actions');

    if (!structurePanel) return;

    // Create validator button
    const button = document.createElement('li');
    button.className = 'bbhv-button settings';
    button.setAttribute('data-balloon', 'HTML Validator');
    button.setAttribute('data-balloon-pos', 'bottom');
    button.setAttribute('tabindex', '0');

    button.innerHTML = `
        <span class="bricks-svg-wrapper" data-name="Visual Validator">
            <svg class="bricks-svg" width="20px" height="20px" stroke="currentColor" fill="currentColor" stroke-width="0" xmlns="http://www.w3.org/2000/svg" viewBox="10 34 212 188"><path d="M160,110h48a14,14,0,0,0,14-14V48a14,14,0,0,0-14-14H160a14,14,0,0,0-14,14V66H128a22,22,0,0,0-22,22v34H70V112A14,14,0,0,0,56,98H24a14,14,0,0,0-14,14v32a14,14,0,0,0,14,14H56a14,14,0,0,0,14-14V134h36v34a22,22,0,0,0,22,22h18v18a14,14,0,0,0,14,14h48a14,14,0,0,0,14-14V160a14,14,0,0,0-14-14H160a14,14,0,0,0-14,14v18H128a10,10,0,0,1-10-10V88a10,10,0,0,1,10-10h18V96A14,14,0,0,0,160,110ZM58,144a2,2,0,0,1-2,2H24a2,2,0,0,1-2-2V112a2,2,0,0,1,2-2H56a2,2,0,0,1,2,2Zm100,16a2,2,0,0,1,2-2h48a2,2,0,0,1,2,2v48a2,2,0,0,1-2,2H160a2,2,0,0,1-2-2Zm0-112a2,2,0,0,1,2-2h48a2,2,0,0,1,2,2V96a2,2,0,0,1-2,2H160a2,2,0,0,1-2-2Z"></path></svg>
        </span>
    `;

    button.addEventListener('click', function () {
        console.log('HTML Validator clicked');
        validateHtmlStructure();
    });

    structurePanel.insertBefore(button, structurePanel.lastElementChild);
}

function createModal() {
    // Create modal HTML structure
    const modal = document.createElement('div');
    modal.id = 'htmlValidatorModal';
    modal.className = 'bbhv-modal';
    
    modal.innerHTML = `
        <div class="bbhv-modal-content">
            <div class="bbhv-modal-header">
                <h2 class="bbhv-modal-title">HTML Tag Validator</h2>

                <button id="bb-htmlValidatorClearHighlights" class="bbhv-clear-btn">Clear All</button>
               <span class="bbhv-close">&times;</span>
            </div>

            <div class="bbhv-tag-category bbhv-tag-category-structure">
                <div class="bbhv-category-title">Structure</div>
                <div class="bbhv-tag-checkboxes">
                    <label class="bbhv-checkbox-label structure-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="header" data-type="structure">
                        
                        <span>header</span>
                    </label>
                    <label class="bbhv-checkbox-label structure-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="nav" data-type="structure">
                        
                        <span>nav</span>
                    </label>
                    <label class="bbhv-checkbox-label structure-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="main" data-type="structure">
                        
                        <span>main</span>
                    </label>
                    <label class="bbhv-checkbox-label structure-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="section" data-type="structure">
                        
                        <span>section</span>
                    </label>
                    <label class="bbhv-checkbox-label structure-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="footer" data-type="structure">
                        
                        <span>footer</span>
                    </label>
                    <label class="bbhv-checkbox-label structure-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="div" data-type="structure">
                        
                        <span>div</span>
                    </label>
                    <label class="bbhv-checkbox-label structure-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="span" data-type="structure">
                        
                        <span>span</span>
                    </label>
                </div>
            </div>

            <div class="bbhv-tag-category bbhv-tag-category-heading">
                <div class="bbhv-category-title">Headings</div>
                <div class="bbhv-tag-checkboxes">
                    <label class="bbhv-checkbox-label heading-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="h1,h2,h3,h4,h5,h6" data-group="headings" data-type="heading">
                       
                        <span>All</span>
                    </label>
                    <label class="bbhv-checkbox-label heading-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="h1" data-type="heading">

                        <span>H1</span>
                    </label>
                    <label class="bbhv-checkbox-label heading-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="h2" data-type="heading">
                        
                        <span>H2</span>
                    </label>
                    <label class="bbhv-checkbox-label heading-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="h3" data-type="heading">
                        
                        <span>H3</span>
                    </label>
                    <label class="bbhv-checkbox-label heading-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="h4" data-type="heading">
                        
                        <span>H4</span>
                    </label>
                    <label class="bbhv-checkbox-label heading-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="h5" data-type="heading">
                        
                        <span>H5</span>
                    </label>
                    <label class="bbhv-checkbox-label heading-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="h6" data-type="heading">
                        
                        <span>H6</span>
                    </label>
                </div>
            </div>

            <div class="bbhv-tag-category bbhv-tag-category-text">
                <div class="bbhv-category-title">Text</div>
                <div class="bbhv-tag-checkboxes">
                    <label class="bbhv-checkbox-label text-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="p" data-type="text">
                        
                        <span>p</span>
                    </label>
                    <label class="bbhv-checkbox-label text-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="br" data-type="text">
                        
                        <span>br</span>
                    </label>
                    <label class="bbhv-checkbox-label text-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="strong" data-type="text">
                        
                        <span>strong</span>
                    </label>
                    <label class="bbhv-checkbox-label text-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="b" data-type="text">
                        
                        <span>b</span>
                    </label>
                    <label class="bbhv-checkbox-label text-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="em" data-type="text">
                        
                        <span>em</span>
                    </label>
                    <label class="bbhv-checkbox-label text-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="i" data-type="text">
                        
                        <span>i</span>
                    </label>
                </div>
            </div>

            <div class="bbhv-tag-category bbhv-tag-category-list">
                <div class="bbhv-category-title">Lists</div>
                <div class="bbhv-tag-checkboxes">
                    <label class="bbhv-checkbox-label list-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="ul,ol,li" data-group="lists" data-type="list">
                        
                        <span>All</span>
                    </label>
                    <label class="bbhv-checkbox-label list-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="ul" data-type="list">
                        
                        <span>ul</span>
                    </label>
                    <label class="bbhv-checkbox-label list-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="ol" data-type="list">
                        
                        <span>ol</span>
                    </label>
                    <label class="bbhv-checkbox-label list-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="li" data-type="list">
                        
                        <span>li</span>
                    </label>
                </div>
            </div>

            <div class="bbhv-tag-category bbhv-tag-category-link">
                <div class="bbhv-category-title">Links/Buttons</div>
                <div class="bbhv-tag-checkboxes">
                    <label class="bbhv-checkbox-label link-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="a" data-type="link">
                        
                        <span>a</span>
                    </label>
                    <label class="bbhv-checkbox-label button-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="button" data-type="link">
                        
                        <span>button</span>
                    </label>
                </div>
            </div>

            <div class="bbhv-tag-category bbhv-tag-category-media">
                <div class="bbhv-category-title">Media</div>
                <div class="bbhv-tag-checkboxes">
                    <label class="bbhv-checkbox-label media-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="img" data-type="media">
                        
                        <span>img</span>
                    </label>
                    <label class="bbhv-checkbox-label media-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="picture" data-type="media">
                        
                        <span>picture</span>
                    </label>
                    <label class="bbhv-checkbox-label media-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="iframe" data-type="media">
                        
                        <span>iframe</span>
                    </label>
                    <label class="bbhv-checkbox-label media-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="video" data-type="media">
                        
                        <span>video</span>
                    </label>
                    <label class="bbhv-checkbox-label media-tag">
                        <input type="checkbox" class="bbhv-tag-checkbox" data-tag="audio" data-type="media">
                        
                        <span>audio</span>
                    </label>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    return modal;
}

function addModalStyles() {
    const styles = `
        <style id="bbhv-styles">
            
        </style>
    `;
    
    document.head.insertAdjacentHTML('beforeend', styles);
}

function getIframeDocument() {
    const iframe = document.getElementById('bricks-builder-iframe');
    if (iframe && iframe.contentDocument) {
        return iframe.contentDocument;
    } else if (iframe && iframe.contentWindow && iframe.contentWindow.document) {
        return iframe.contentWindow.document;
    }
    return null;
}

function addStylesToIframe() {
    const iframeDoc = getIframeDocument();
    if (iframeDoc) {
        // Check if styles already exist in iframe
        if (!iframeDoc.getElementById('bbhv-iframe-styles')) {
            const styles = `
                <style id="bbhv-iframe-styles">
                    :root {
                    --bbhv-category-structure-color: #5f33fd;
                    --bbhv-category-heading-color: #fdf633;
                    --bbhv-category-text-color: #2ed573;
                    --bbhv-category-list-color: #a55eea;
                    --bbhv-category-link-color: #ffa502;
                    --bbhv-category-media-color: #ff4757;
                    
                    --bbhv-outline-width: 2px;
                    }

                    .bbhv-structure-border {
                        outline: var(--bbhv-outline-width) solid var(--bbhv-category-structure-color) !important;
                    }

                    .bbhv-structure-border::after {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                    }

                    .bbhv-heading-border {
                        outline: var(--bbhv-outline-width) solid var(--bbhv-category-heading-color) !important;
                    }

                    .bbhv-text-border {
                        outline: var(--bbhv-outline-width) solid var(--bbhv-category-text-color) !important;
                    }

                    .bbhv-list-border {
                        outline: var(--bbhv-outline-width) solid var(--bbhv-category-list-color) !important;
                    }

                    .bbhv-link-border {
                        outline: var(--bbhv-outline-width) solid var(--bbhv-category-link-color) !important;
                    }

                    .bbhv-media-border {
                        outline: var(--bbhv-outline-width) solid var(--bbhv-category-media-color) !important;
                    }

                </style>
            `;
            iframeDoc.head.insertAdjacentHTML('beforeend', styles);
        }
    }
}

function setupModalEventListeners(modal) {
    const closeBtn = modal.querySelector('.bbhv-close');
    const clearHighlightsBtn = modal.querySelector('#bb-htmlValidatorClearHighlights');
    const checkboxes = modal.querySelectorAll('.bbhv-tag-checkbox');

    // Close modal events
    closeBtn.addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Clear all highlights
    clearHighlightsBtn.addEventListener('click', function() {
        clearAllHighlights();
        
        // Remove checked state from all checkboxes
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    });

    // Checkbox functionality
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            clearAllHighlights();
            
            // Get all checked checkboxes
            const checkedBoxes = modal.querySelectorAll('.bbhv-tag-checkbox:checked');
            
            if (checkedBoxes.length > 0) {
                // Add styles to iframe if not already present
                addStylesToIframe();
                
                checkedBoxes.forEach(checkedBox => {
                    const tagSelector = checkedBox.getAttribute('data-tag');
                    const tagType = checkedBox.getAttribute('data-type');
                    highlightElements(tagSelector, tagType);
                });
            }
        });
    });
}

function openModal() {
    const modal = document.getElementById('htmlValidatorModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModal() {
    clearAllHighlights();
    const modal = document.getElementById('htmlValidatorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function highlightElements(tagSelector, tagType) {
    const iframeDoc = getIframeDocument();
    if (iframeDoc) {
        const borderClass = `bbhv-${tagType}-border`;
        const targetArea = iframeDoc.querySelector('#brx-content.bricks-area');
        
        // Get all matching elements
        const elements = targetArea ? targetArea.querySelectorAll(tagSelector) : iframeDoc.querySelectorAll(tagSelector);
        const count = elements.length;
        
        // Update checkbox label with count
        updateCheckboxLabel(tagSelector, count);
        
        // Highlight elements
        elements.forEach(element => {
            element.classList.add(borderClass);
        });
        
        console.log(`Highlighted ${count} ${tagSelector} elements with ${borderClass}`);
        return count;
    } else {
        console.warn('Could not access iframe document');
        return 0;
    }
}

function updateCheckboxLabel(tagSelector, count) {
    const checkboxes = document.querySelectorAll(`.bbhv-tag-checkbox[data-tag="${tagSelector}"]`);
    checkboxes.forEach(checkbox => {
        const label = checkbox.closest('label');
        if (label) {
            // Remove existing count if any
            const existingCount = label.querySelector('.tag-count');
            if (existingCount) {
                existingCount.remove();
            }
            
            // Add count badge if elements found
            if (count > 0) {
                const countSpan = document.createElement('span');
                countSpan.className = 'tag-count';
                countSpan.textContent = ` (${count})`;
                label.appendChild(countSpan);
            }            
        }
    });
}

function validateHtmlStructure() {
    // Check if iframe is accessible
    const iframeDoc = getIframeDocument();
    if (!iframeDoc) {
        console.error('Cannot access iframe document');
        return;
    }

    // Check if modal already exists
    let modal = document.getElementById('htmlValidatorModal');
    
    if (!modal) {
        // Create modal first
        modal = createModal();
        
        // Then add styles
        if (!document.getElementById('bbhv-styles')) {
            addModalStyles();
        }
        
        // Setup event listeners
        setupModalEventListeners(modal);
    }
    
    // Show modal
    modal.style.display = 'block';
    
    // Add styles to iframe if needed
    addStylesToIframe();
}

function clearAllHighlights() {
    const iframeDoc = getIframeDocument();
    if (iframeDoc) {
        // Remove all possible highlight classes
        const highlightClasses = [
            'bbhv-structure-border',
            'bbhv-heading-border',
            'bbhv-text-border',
            'bbhv-list-border',
            'bbhv-link-border',
            'bbhv-media-border'
        ];
        
        let totalCleared = 0;
        highlightClasses.forEach(className => {
            const elements = iframeDoc.querySelectorAll(`.${className}`);
            elements.forEach(element => {
                element.classList.remove(className);
                totalCleared++;
            });
        });
        
        console.log(`Cleared ${totalCleared} highlighted elements from iframe`);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for iframe to load
    setTimeout(initHtmlValidator, 1000);
});

// Keyboard support
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('htmlValidatorModal');
        if (modal && modal.style.display === 'block') {
            closeModal();
        }
    }
});

// Monitor iframe load state
function waitForIframe() {
    const iframe = document.getElementById('bricks-builder-iframe');
    if (iframe) {
        iframe.addEventListener('load', function() {
            console.log('Bricks builder iframe loaded');
            // Add styles to iframe when it loads
            setTimeout(addStylesToIframe, 100);
        });
    }
}

// Initialize iframe monitoring
document.addEventListener('DOMContentLoaded', waitForIframe);