import {Controller} from '@hotwired/stimulus';
import {useDebounce} from 'stimulus-use';
import axios from 'axios';

import React from 'react';
import { createRoot } from 'react-dom/client';

import Editor from '@monaco-editor/react';

export default class extends Controller {
  static values = {
    value: String,
    liveUri: String
  };
  static targets = ['iframe', 'input', 'editor'];
  static debounces = [
    {
      name: 'updatePreview',
      wait: 500
    }
  ]


  connect() {
    useDebounce(this);

    const root = createRoot(this.editorTarget);
    root.render(<App value={this.valueValue} onChange={value => this.handleChanges(value)}/>);
  }

  handleChanges(value) {
    this.inputTarget.value = value;
    this.updatePreview();
  }

  async updatePreview() {
    const preview = await this.fetchUpdate(this.inputTarget.value, this.liveUriValue);

    const iframeDocument = this.iframeTarget.contentDocument || this.iframeTarget.contentWindow.document;
    iframeDocument.open();
    iframeDocument.write(preview);
    iframeDocument.close();
  }

  async fetchUpdate(value, uri) {
    const response = await axios.post(uri, {
      update: value
    });

    return response.data;
  }
}

function App(props) {

  function handleEditorChange(value, event) {
    props.onChange(value);
  }

  function handleEditorDidMount(editor, monaco) {
    // console.log(editor.getModel());
  }

  return <Editor onChange={handleEditorChange}
                 onMount={handleEditorDidMount}
                 height="550px"
                 theme="vs-dark"
                 defaultLanguage="twig"
                 defaultValue={props.value}
  />;
}
