import { Controller } from '@hotwired/stimulus';
import ace from 'brace';

require('brace/mode/twig');
require('brace/theme/monokai');

export default class extends Controller {
  static targets = ['input', 'editor'];

  connect () {
    this.editor = ace.edit(this.editorTarget);
    this.editor.getSession().setMode('ace/mode/twig');
    this.editor.setTheme('ace/theme/monokai');

    this.editor.getSession().on('change', () => {
      this.inputTarget.value = this.editor.getSession().getValue();
    });
  }

  disconnect () {
    this.editor.destroy();
  }

}
