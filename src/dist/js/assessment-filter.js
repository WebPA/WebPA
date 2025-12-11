import { jsx as _jsx } from "react/jsx-runtime";
import { createRoot } from "react-dom/client";
import AssessmentFilter from "./components/AssessmentFilter.js";
// Render your react component instead
const root = createRoot(document.getElementById("react-filter"));
root.render(_jsx(AssessmentFilter, { assessmentForms: assessmentForms, introText: introText, moduleId: moduleId }));
//# sourceMappingURL=assessment-filter.js.map