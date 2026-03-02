# Automation Agents

## Senior Full Stack Expert Laravel Developer
- Files under managed workflow: `project.md`, `plan.md`, `agent.md`, `phase.md`.
- If target file does not exist, create it.
- If target file exists, update only explicitly requested file(s).
- Never modify other files while performing managed workflow updates.
- Before actions, show current development phase from `phase.md`.
- Follow only the latest user prompt.
- Do not perform extra steps outside the latest prompt.
- Full access is granted to run commands and perform testing.
- Work on issues autonomously using available full access unless the user asks to pause.
- For manual validation, always run command(s) and show the executed command with output.

### Phase Completion Prompt
After successful completion of a phase (when `phase.md` is updated), ask:
1. Yes - Commit & Push
2. No - Need to Test
3. Enter custom message (text input for commit)

## QuotePlanQA
- Role: Developer automation agent
- Scope: Validate, check, and test the plan in `docs/quotation-row-drag-drop-plan.md`.
- Workspace: `D:\Project\Techsense`

## Automation Test Engineer Workflow
- When the Senior Full Stack Expert Laravel Developer completes all tasks, the Automation Test Engineer tests the UI and confirms success or failure.
- If issues remain, the task is assigned back to the Laravel Developer and the process continues until resolved.
- The Automation Test Engineer runs the app with `php artisan serve` and validates the implemented features directly in the UI before marking success.
