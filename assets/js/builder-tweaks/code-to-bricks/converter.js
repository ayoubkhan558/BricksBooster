function initCode2Bricks() {
    // Find the Bricks toolbar UL element
    const toolbar = document.querySelector('#bricks-toolbar > ul.group-wrapper:nth-child(1)');

    if (toolbar) {
        // Create new button LI element
        const button = document.createElement('li');
        button.className = 'code-to-bricks-button settings';
        button.setAttribute('data-balloon', 'Convert HTML to Bricks');
        button.setAttribute('data-balloon-pos', 'bottom');
        button.setAttribute('tabindex', '0');

        // Button HTML
        button.innerHTML = `
                <span class="bricks-svg-wrapper">
                    <div class="icon-code" style="width: 20px;">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 18L22 12L16 6"></path>
                            <path d="M8 6L2 12L8 18"></path>
                        </svg>
                    </div>
                </span>
            `;

        // Add click handler
        button.addEventListener('click', function () {
            console.log('Code to Bricks button clicked!');
            // Add your conversion logic here
        });

        // Insert button before the last element (elements/component button)
        toolbar.insertBefore(button, toolbar.lastElementChild);
    }
}

// Initialize when Bricks builder is ready
document.addEventListener('DOMContentLoaded', function () {
    console.log('BricksBooster: Code to Bricks initialized');
    initCode2Bricks();
});
