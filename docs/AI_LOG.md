# AI Log

## Milestone 1

### Tools and tasks

- **Work already in place before this AI-assisted pass:** the Trackify project, its initial frontend screens and styling, static demo content, image assets, and draft ERD. These were the starting point for the milestone review, not generated from scratch in this session.
- **Student's role:** supplied the existing project and milestone scope. The student is responsible for reviewing, understanding, and deciding which proposed changes to keep before submission.
- **Tool:** Copilot AI assistant in VS Code; the model name was not recorded in this session.
- **Task:** Check the pre-existing Trackify project against the supplied milestone and lab PDFs and help close identified M1 gaps.
- **Main prompt excerpt:** “I already made the project, but need help making it fullfill all the milestone 1 requirements…”
- **AI assistance:** limited to this M1 pass: reviewed the rubric, reorganized the existing frontend into the required folders, repaired SPA navigation and dashboard interactions, added the missing static views, and scaffolded the required project and AI documentation. The existing project concept, initial screens, styling, assets, and ERD were already in place and were not created in this session.

### Where the AI was wrong or needed correction

1. The first local PDF-extraction script wrote extracted typographic characters using the Windows console's default encoding and failed with a `UnicodeEncodeError`. The failure was visible in the command output. The script was corrected to write UTF-8 output; the PDFs remained local.
2. An initial multi-file patch expected an HTML script block that did not match the actual `frontend/index.html` contents, so the patch failed and made no changes. The failure was checked, and the edits were split into smaller patches using the file's actual contents.

These are the two concrete issues encountered during this AI-assisted task. Add any further code-generation mistakes you find during your own review, including what was wrong, how you identified it, and how you corrected it.
