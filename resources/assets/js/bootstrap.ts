import _ = require("lodash");
import axios from 'axios';
import 'bootstrap';

declare let window: {
    _: any
    axios: any
}

window._ = _;

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

const token = document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
