"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const jsx_runtime_1 = require("react/jsx-runtime");
const react_1 = require("react");
function AssessmentFilter({ assessmentForms, introText, moduleId, }) {
    const [search, setSearch] = (0, react_1.useState)("");
    const filteredForms = assessmentForms.filter((item) => item.module_title.toLowerCase().includes(search.trim()));
    return ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsx)("h2", { children: "Your assessment forms" }), (0, jsx_runtime_1.jsx)("div", { className: "form_section", children: (0, jsx_runtime_1.jsx)("table", { cellPadding: "0", cellSpacing: "0", children: (0, jsx_runtime_1.jsxs)("tbody", { children: [(0, jsx_runtime_1.jsxs)("tr", { children: [(0, jsx_runtime_1.jsx)("td", { children: (0, jsx_runtime_1.jsx)("label", { className: "small", htmlFor: "module-search", children: "Filter by module name" }) }), (0, jsx_runtime_1.jsx)("td", { children: (0, jsx_runtime_1.jsx)("input", { id: "module-search", name: "module-search", value: search, onChange: (e) => setSearch(e.target.value) }) })] }), filteredForms.map((item) => ((0, jsx_runtime_1.jsxs)("tr", { children: [(0, jsx_runtime_1.jsx)("td", { children: (0, jsx_runtime_1.jsx)("input", { type: "radio", name: "form_id", id: `form_${item.form_id}`, value: item.form_id }) }), (0, jsx_runtime_1.jsx)("td", { children: (0, jsx_runtime_1.jsxs)("label", { className: "small", htmlFor: `form_${item.form_id}`, children: [item.form_name, " ", moduleId === item.module_id
                                                    ? ""
                                                    : `(${item.module_title} ${item.module_code})`] }) }), (0, jsx_runtime_1.jsxs)("td", { children: ["\u00A0 \u00A0 (", (0, jsx_runtime_1.jsx)("a", { style: { fontWeight: "normal", fontSize: "84%" }, href: `../../forms/edit/preview_form.php?f=${item.form_id}&amp;i=${introText}`, target: "_blank", children: "preview" }), ")"] })] }, item.form_id)))] }) }) })] }));
}
exports.default = AssessmentFilter;
//# sourceMappingURL=AssessmentFilter.js.map