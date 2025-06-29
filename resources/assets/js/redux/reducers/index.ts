import {combineReducers} from "redux";

import generals from "./generals";
import auth from "./auth";

export default combineReducers({
    generals,
    auth
});
