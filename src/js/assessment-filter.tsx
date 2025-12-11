import { createRoot } from "react-dom/client";
import AssessmentFilter from "./components/AssessmentFilter.js";
import type AssessmentForm from "./interfaces/assessmentForms.js";

declare const assessmentForms: AssessmentForm[];
declare const introText: string;
declare const moduleId: number;

// Render your react component instead
const root = createRoot(document.getElementById("react-filter")!);

root.render(
  <AssessmentFilter
    assessmentForms={assessmentForms}
    introText={introText}
    moduleId={moduleId}
  />,
);
