import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useState } from "react";
function AssessmentFilter({ assessmentForms, introText, moduleId, }) {
    const [search, setSearch] = useState("");
    const filteredForms = assessmentForms.filter((item) => item.module_title.toLowerCase().includes(search.trim()));
    return (_jsxs(_Fragment, { children: [_jsx("h2", { children: "Your assessment forms" }), _jsx("div", { className: "form_section", children: _jsx("table", { cellPadding: "0", cellSpacing: "0", children: _jsxs("tbody", { children: [_jsxs("tr", { children: [_jsx("td", { children: _jsx("label", { className: "small", htmlFor: "module-search", children: "Filter by module name" }) }), _jsx("td", { children: _jsx("input", { id: "module-search", name: "module-search", value: search, onChange: (e) => setSearch(e.target.value) }) })] }), filteredForms.map((item) => (_jsxs("tr", { children: [_jsx("td", { children: _jsx("input", { type: "radio", name: "form_id", id: `form_${item.form_id}`, value: item.form_id }) }), _jsx("td", { children: _jsxs("label", { className: "small", htmlFor: `form_${item.form_id}`, children: [item.form_name, " ", moduleId === item.module_id
                                                    ? ""
                                                    : `(${item.module_title} ${item.module_code})`] }) }), _jsxs("td", { children: ["\u00A0 \u00A0 (", _jsx("a", { style: { fontWeight: "normal", fontSize: "84%" }, href: `../../forms/edit/preview_form.php?f=${item.form_id}&amp;i=${introText}`, target: "_blank", children: "preview" }), ")"] })] }, item.form_id)))] }) }) })] }));
}
export default AssessmentFilter;
