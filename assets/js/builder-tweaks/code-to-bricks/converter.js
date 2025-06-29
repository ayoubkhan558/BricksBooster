// Code2Bricks Converter
function initCode2Bricks() {
    console.log('BricksBooster: Code2Bricks initialized');

    // Your conversion logic here
}

// Initialize when builder is ready
document.addEventListener('DOMContentLoaded', initCode2Bricks);


// Code2Bricks - HTML to Bricks converter
export function initCode2Bricks() {
    console.log('BricksStack: Code2Bricks initialized');
    
    // Setup panel button functionality
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('bricks-stack-convert-button')) {
        const html = document.querySelector('.bricks-stack-code-input').value;
        if (html) {
          convertHTMLToBricks(html);
        }
      }
    });
  }
  
  function convertHTMLToBricks(html) {
    // AJAX call to convert HTML
    wp.ajax.post('code2bricks_convert', {
      html,
      _ajax_nonce: code2bricksData.nonce
    }).then(response => {
      console.log('Conversion successful', response);
      // Handle response (insert elements into canvas)
    }).catch(error => {
      console.error('Conversion failed', error);
    });
  }
  
  // Initialize when builder is ready
  document.addEventListener('bricks:builder:ready', initCode2Bricks);
  