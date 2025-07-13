import {combineReducers} from "redux";

import generals from "./generals";
import auth from "./auth";
import project from "./project";

export default combineReducers({
    generals,
    auth,
    project
});
