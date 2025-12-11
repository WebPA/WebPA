"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const jsx_runtime_1 = require("react/jsx-runtime");
const client_1 = require("react-dom/client");
const AssessmentFilter_js_1 = __importDefault(require("./components/AssessmentFilter.js"));
// Render your react component instead
const root = (0, client_1.createRoot)(document.getElementById("react-filter"));
root.render((0, jsx_runtime_1.jsx)(AssessmentFilter_js_1.default, { assessmentForms: assessmentForms, introText: introText, moduleId: moduleId }));
//# sourceMappingURL=assessment-filter.js.map