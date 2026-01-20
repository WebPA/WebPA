import { useState } from "react";
import type AssessmentForm from "../interfaces/assessmentForms.js";

function AssessmentFilter({
  assessmentForms,
  introText,
  moduleId,
}: {
  assessmentForms: AssessmentForm[];
  introText: string;
  moduleId: number;
}) {
  const [search, setSearch] = useState("");

  const filteredForms = assessmentForms.filter((item) =>
    item.module_title.toLowerCase().includes(search.trim()),
  );

  return (
    <>
      <h2>Your assessment forms</h2>
      <div className="form_section">
        <table cellPadding="0" cellSpacing="0">
          <tbody>
            <tr>
              <td>
                <label className="small" htmlFor="module-search">
                  Filter by module name
                </label>
              </td>
              <td>
                <input
                  id="module-search"
                  name="module-search"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
              </td>
            </tr>
            {filteredForms.map((item) => (
              <tr key={item.form_id}>
                <td>
                  <input
                    type="radio"
                    name="form_id"
                    id={`form_${item.form_id}`}
                    value={item.form_id}
                  />
                </td>
                <td>
                  <label className="small" htmlFor={`form_${item.form_id}`}>
                    {item.form_name}{" "}
                    {moduleId === item.module_id
                      ? ""
                      : `(${item.module_title} ${item.module_code})`}
                  </label>
                </td>
                <td>
                  &nbsp; &nbsp; (
                  <a
                    style={{ fontWeight: "normal", fontSize: "84%" }}
                    href={`../../forms/edit/preview_form.php?f=${item.form_id}&amp;i=${introText}`}
                    target="_blank"
                  >
                    preview
                  </a>
                  )
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}

export default AssessmentFilter;
