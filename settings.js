/**
 * @var {object} wp_maintenance - Global object.
 * @property {object} htmlCodeEditor
 * @property {object} cssCodeEditor
 * @property {object} jsCodeEditor
 */

document.addEventListener('DOMContentLoaded', function() {
  if (typeof wp_maintenance !== 'object' || !window.wp || !wp.codeEditor) {
    return;
  }

  const editors = ['html', 'css', 'js'];

  editors.forEach(editor => {
    const element = document.getElementById('maintenance_' + editor);

    if (element && wp_maintenance[editor + 'CodeEditor']) {
      wp.codeEditor.initialize(element, wp_maintenance[editor + 'CodeEditor']);
    }
  });
});
