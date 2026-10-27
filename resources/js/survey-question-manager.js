const clone = (value) => JSON.parse(JSON.stringify(value));

const newOption = (index = 0) => ({
    stableId: null,
    label: '',
    icon: 'message-circle',
    tone: ['blue', 'green', 'orange', 'purple', 'red'][index % 5],
    groupName: '',
    clientId: `option-${crypto.randomUUID()}`,
});

const normalizedEditor = (editor) => ({
    stableId: editor.stableId,
    prompt: editor.prompt,
    helpText: editor.helpText,
    type: editor.type,
    sectionStableId: editor.sectionStableId,
    position: Number(editor.position),
    isRequired: Boolean(editor.isRequired),
    isActive: Boolean(editor.isActive),
    maxSelections: Number(editor.maxSelections || 1),
    options: editor.options.map((option) => ({
        stableId: option.stableId,
        label: option.label,
        icon: option.icon,
        tone: option.tone,
        groupName: option.groupName,
    })),
});

export const surveyQuestionManager = (initialState, configuration) => ({
    state: clone(initialState),
    routes: configuration.routes,
    types: configuration.types,
    icons: configuration.icons,
    tones: configuration.tones,
    selectedSection: 'all',
    collapsedSections: {},
    addMenuOpen: false,
    openSectionMenu: null,
    editorOpen: true,
    editorMode: 'create',
    editorTab: 'basic',
    editor: null,
    editorSnapshot: '',
    formErrors: {},
    saving: false,
    publishing: false,
    sectionModal: null,
    confirmation: null,
    preview: null,
    previewDevice: 'desktop',
    previewSelections: {},
    toasts: [],
    drag: null,
    pointerMoveHandler: null,
    pointerUpHandler: null,
    previouslyFocusedElement: null,

    init() {
        try {
            this.collapsedSections = JSON.parse(localStorage.getItem('bec-survey-collapsed-sections') ?? '{}');
        } catch {
            this.collapsedSections = {};
        }

        this.startNewQuestion(this.state.sections[0]?.stableId, true);
        this.beforeUnloadHandler = (event) => {
            if (!this.isDirty) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        };
        window.addEventListener('beforeunload', this.beforeUnloadHandler);
    },

    destroy() {
        window.removeEventListener('beforeunload', this.beforeUnloadHandler);
        this.removePointerListeners();
    },

    get filteredSections() {
        if (this.selectedSection === 'all') {
            return this.state.sections;
        }

        return this.state.sections.filter((section) => section.stableId === this.selectedSection);
    },

    get isDirty() {
        return this.editor && JSON.stringify(normalizedEditor(this.editor)) !== this.editorSnapshot;
    },

    get editorTitle() {
        return this.editorMode === 'edit' ? 'Edit Question' : 'Add New Question';
    },

    get positionOptions() {
        const section = this.sectionByStableId(this.editor?.sectionStableId);

        if (!section) {
            return [1];
        }

        const isAlreadyInSection = this.editorMode === 'edit'
            && section.questions.some((question) => question.stableId === this.editor.stableId);
        const maximum = section.questions.length + (isAlreadyInSection ? 0 : 1);

        return Array.from({ length: Math.max(1, maximum) }, (_, index) => index + 1);
    },

    sectionByStableId(stableId) {
        return this.state.sections.find((section) => section.stableId === stableId);
    },

    questionByStableId(stableId) {
        for (const section of this.state.sections) {
            const question = section.questions.find((item) => item.stableId === stableId);

            if (question) {
                return question;
            }
        }

        return null;
    },

    questionNumber(stableId) {
        const questions = this.state.sections.flatMap((section) => section.questions);

        return questions.findIndex((question) => question.stableId === stableId) + 1;
    },

    questionTypeLabel(type) {
        return this.types[type] ?? type;
    },

    optionError(index, field) {
        return this.formErrors[`options.${index}.${field}`]?.[0] ?? '';
    },

    startNewQuestion(sectionStableId = null, force = false) {
        if (!force && !this.confirmDiscard()) {
            return;
        }

        const section = this.sectionByStableId(sectionStableId)
            ?? this.sectionByStableId(this.selectedSection)
            ?? this.state.sections[0];

        if (!section) {
            this.openSectionEditor();

            return;
        }

        this.editorMode = 'create';
        this.editorTab = 'basic';
        this.formErrors = {};
        this.editor = {
            id: null,
            stableId: null,
            prompt: '',
            helpText: '',
            type: 'single_choice',
            sectionStableId: section.stableId,
            position: section.questions.length + 1,
            isRequired: true,
            isActive: true,
            maxSelections: 3,
            options: [newOption(0), newOption(1)],
        };
        this.editorSnapshot = JSON.stringify(normalizedEditor(this.editor));
        this.editorOpen = true;
        this.addMenuOpen = false;
        this.$nextTick(() => this.$refs.questionText?.focus());
    },

    editQuestion(question, force = false) {
        if (!force && !this.confirmDiscard()) {
            return;
        }

        this.editorMode = 'edit';
        this.editorTab = 'basic';
        this.formErrors = {};
        this.editor = {
            id: question.id,
            stableId: question.stableId,
            prompt: question.prompt,
            helpText: question.helpText,
            type: question.type,
            sectionStableId: question.sectionStableId,
            position: question.position,
            isRequired: question.isRequired,
            isActive: question.isActive,
            maxSelections: question.maxSelections ?? 3,
            options: question.options.map((option) => ({
                ...clone(option),
                clientId: option.stableId ?? `option-${crypto.randomUUID()}`,
            })),
        };
        this.editorSnapshot = JSON.stringify(normalizedEditor(this.editor));
        this.editorOpen = true;
        this.openSectionMenu = null;
        this.$nextTick(() => this.$refs.questionText?.focus());
    },

    confirmDiscard() {
        return !this.isDirty || window.confirm('Discard your unsaved question changes?');
    },

    cancelEditor() {
        if (!this.confirmDiscard()) {
            return;
        }

        if (window.innerWidth < 1280) {
            this.editorOpen = false;
        }

        this.startNewQuestion(this.editor?.sectionStableId, true);
    },

    setQuestionType(type) {
        this.editor.type = type;

        if (type !== 'long_text' && this.editor.options.length < 2) {
            this.editor.options = [newOption(0), newOption(1)];
        }

        if (type === 'long_text') {
            this.editorTab = 'basic';
        }
    },

    addOption() {
        this.editor.options.push(newOption(this.editor.options.length));
    },

    removeOption(index) {
        if (this.editor.options.length <= 2) {
            this.toast('Choice questions need at least two options.', 'error');

            return;
        }

        this.editor.options.splice(index, 1);
    },

    moveOption(index, direction) {
        const target = direction === 'up' ? index - 1 : index + 1;

        if (target < 0 || target >= this.editor.options.length) {
            return;
        }

        const [option] = this.editor.options.splice(index, 1);
        this.editor.options.splice(target, 0, option);
    },

    async submitQuestion() {
        if (this.saving || !this.editor) {
            return;
        }

        this.saving = true;
        this.formErrors = {};
        const payload = {
            section_stable_id: this.editor.sectionStableId,
            position: Number(this.editor.position),
            prompt: this.editor.prompt,
            help_text: this.editor.helpText || null,
            type: this.editor.type,
            is_required: this.editor.isRequired,
            is_active: this.editor.isActive,
            max_selections: this.editor.type === 'multiple_choice' ? Number(this.editor.maxSelections) : null,
            options: this.editor.type === 'long_text' ? [] : this.editor.options.map((option) => ({
                stable_id: option.stableId,
                label: option.label,
                icon: option.icon,
                tone: option.tone,
                group_name: this.editor.type === 'grouped_choice' ? option.groupName : null,
            })),
        };
        const url = this.editorMode === 'edit'
            ? `${this.routes.questions}/${this.editor.id}`
            : this.routes.questions;

        try {
            const data = await this.request(url, this.editorMode === 'edit' ? 'PATCH' : 'POST', payload, true);
            this.applyState(data.state);
            const savedQuestion = this.questionByStableId(data.focusStableId);

            if (savedQuestion) {
                this.editQuestion(savedQuestion, true);
            }

            this.toast(data.message);
        } catch (error) {
            this.toast(error.message, 'error');
        } finally {
            this.saving = false;
        }
    },

    askDeleteQuestion(question) {
        this.confirmation = {
            title: 'Delete this question?',
            message: 'It will be removed from the next published survey. Historical student responses and their original question text will remain intact.',
            confirmLabel: 'Delete Question',
            danger: true,
            action: () => this.deleteQuestion(question),
        };
    },

    async deleteQuestion(question) {
        this.confirmation = null;

        try {
            const data = await this.request(`${this.routes.questions}/${question.id}`, 'DELETE');
            this.applyState(data.state);

            if (this.editor?.stableId === question.stableId) {
                this.startNewQuestion(question.sectionStableId, true);
            }

            this.toast(data.message);
        } catch (error) {
            this.toast(error.message, 'error');
        }
    },

    askDuplicateQuestion(question) {
        this.confirmation = {
            title: 'Duplicate this question?',
            message: 'A new independent draft question and new option identifiers will be placed immediately after the original.',
            confirmLabel: 'Duplicate Question',
            danger: false,
            action: () => this.duplicateQuestion(question),
        };
    },

    async duplicateQuestion(question) {
        this.confirmation = null;

        try {
            const data = await this.request(`${this.routes.questions}/${question.id}/duplicate`, 'POST');
            this.applyState(data.state);
            const copy = this.questionByStableId(data.focusStableId);

            if (copy) {
                this.editQuestion(copy, true);
            }

            this.toast(data.message);
        } catch (error) {
            this.toast(error.message, 'error');
        }
    },

    async moveQuestion(question, direction) {
        try {
            const data = await this.request(`${this.routes.questions}/${question.id}/move`, 'PATCH', { direction });
            this.applyState(data.state);
            this.toast(data.message);
        } catch (error) {
            this.toast(error.message, 'error');
        }
    },

    openSectionEditor(section = null) {
        this.addMenuOpen = false;
        this.openSectionMenu = null;
        this.sectionModal = section
            ? {
                mode: 'edit',
                id: section.id,
                stableId: section.stableId,
                title: section.title,
                description: section.description,
                position: section.position,
                isActive: section.isActive,
                errors: {},
                saving: false,
            }
            : {
                mode: 'create',
                id: null,
                stableId: null,
                title: '',
                description: '',
                position: this.state.sections.length + 1,
                isActive: true,
                errors: {},
                saving: false,
            };
        this.previouslyFocusedElement = document.activeElement;
        this.$nextTick(() => this.$refs.sectionTitle?.focus());
    },

    async saveSection() {
        if (this.sectionModal.saving) {
            return;
        }

        this.sectionModal.saving = true;
        this.sectionModal.errors = {};
        const url = this.sectionModal.mode === 'edit'
            ? `${this.routes.sections}/${this.sectionModal.id}`
            : this.routes.sections;

        try {
            const data = await this.request(url, this.sectionModal.mode === 'edit' ? 'PATCH' : 'POST', {
                title: this.sectionModal.title,
                description: this.sectionModal.description || null,
                position: Number(this.sectionModal.position),
                is_active: this.sectionModal.isActive,
            }, true, 'section');
            this.applyState(data.state);
            this.sectionModal = null;
            this.toast(data.message);
            this.restoreFocus();
        } catch (error) {
            this.toast(error.message, 'error');
        } finally {
            if (this.sectionModal) {
                this.sectionModal.saving = false;
            }
        }
    },

    askDeleteSection(section) {
        this.openSectionMenu = null;
        this.confirmation = {
            title: 'Archive this section?',
            message: section.questionCount > 0
                ? 'Move or delete every question in this section before archiving it.'
                : 'The empty section will be removed from the draft survey.',
            confirmLabel: section.questionCount > 0 ? 'Close' : 'Archive Section',
            danger: section.questionCount === 0,
            action: section.questionCount > 0
                ? () => { this.confirmation = null; }
                : () => this.deleteSection(section),
        };
    },

    async deleteSection(section) {
        this.confirmation = null;

        try {
            const data = await this.request(`${this.routes.sections}/${section.id}`, 'DELETE');
            this.applyState(data.state);
            this.toast(data.message);
        } catch (error) {
            this.toast(error.message, 'error');
        }
    },

    async moveSection(section, direction) {
        this.openSectionMenu = null;

        try {
            const data = await this.request(`${this.routes.sections}/${section.id}/move`, 'PATCH', { direction });
            this.applyState(data.state);
            this.toast(data.message);
        } catch (error) {
            this.toast(error.message, 'error');
        }
    },

    askPublish() {
        this.confirmation = {
            title: 'Publish draft changes?',
            message: `Version ${this.state.version.number} will become the active student survey. The current published version and all of its responses will remain available in history.`,
            confirmLabel: 'Publish Changes',
            danger: false,
            action: () => this.publish(),
        };
    },

    async publish() {
        if (this.publishing) {
            return;
        }

        this.confirmation = null;
        this.publishing = true;

        try {
            const data = await this.request(this.routes.publish, 'POST');
            this.applyState(data.state);
            this.startNewQuestion(this.editor?.sectionStableId, true);
            this.toast(data.message);
        } catch (error) {
            this.toast(error.message, 'error');
        } finally {
            this.publishing = false;
        }
    },

    previewQuestion(question = null) {
        const previewQuestion = question
            ? clone(question)
            : {
                ...clone(this.editor),
                options: clone(this.editor.options),
            };
        const section = this.sectionByStableId(previewQuestion.sectionStableId);
        const number = question
            ? this.questionNumber(question.stableId)
            : this.previewEditorNumber();

        this.preview = {
            ...previewQuestion,
            prompt: previewQuestion.prompt.trim() || 'Add question text to preview this question.',
            helpText: previewQuestion.helpText.trim(),
            sectionTitle: section?.title ?? 'Survey section',
            number: Math.max(1, number),
        };
        this.previewSelections = previewQuestion.type === 'grouped_choice' ? {} : [];
        this.previouslyFocusedElement = document.activeElement;
        this.$nextTick(() => this.$refs.previewClose?.focus());
    },

    previewEditorNumber() {
        const sectionIndex = this.state.sections.findIndex((section) => section.stableId === this.editor.sectionStableId);
        const previousQuestions = this.state.sections
            .slice(0, Math.max(0, sectionIndex))
            .reduce((total, section) => total + section.questions.length, 0);

        return previousQuestions + Number(this.editor.position || 1);
    },

    previewProgress() {
        const total = Math.max(1, this.state.totalQuestions + (this.editorMode === 'create' ? 1 : 0));

        return Math.min(100, Math.round((this.preview.number / total) * 100));
    },

    previewGroups() {
        return [...new Set(this.preview.options.map((option) => option.groupName).filter(Boolean))];
    },

    selectPreviewOption(option) {
        if (this.preview.type === 'single_choice') {
            this.previewSelections = [option.clientId ?? option.stableId ?? option.id];

            return;
        }

        if (this.preview.type === 'grouped_choice') {
            this.previewSelections[option.groupName] = option.clientId ?? option.stableId ?? option.id;

            return;
        }

        const optionId = option.clientId ?? option.stableId ?? option.id;
        const selectedIndex = this.previewSelections.indexOf(optionId);

        if (selectedIndex !== -1) {
            this.previewSelections.splice(selectedIndex, 1);

            return;
        }

        if (this.preview.maxSelections && this.previewSelections.length >= this.preview.maxSelections) {
            this.toast(`Choose up to ${this.preview.maxSelections} options.`, 'error');

            return;
        }

        this.previewSelections.push(optionId);
    },

    previewOptionSelected(option) {
        const optionId = option.clientId ?? option.stableId ?? option.id;

        if (this.preview.type === 'grouped_choice') {
            return this.previewSelections[option.groupName] === optionId;
        }

        return this.previewSelections.includes(optionId);
    },

    closePreview() {
        this.preview = null;
        this.restoreFocus();
    },

    closeSectionModal() {
        this.sectionModal = null;
        this.restoreFocus();
    },

    restoreFocus() {
        this.$nextTick(() => this.previouslyFocusedElement?.focus?.());
    },

    trapFocus(event) {
        if (event.key !== 'Tab') {
            return;
        }

        const focusable = [...event.currentTarget.querySelectorAll(
            'button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href]',
        )].filter((element) => element.offsetParent !== null);

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },

    closeOnEscape() {
        if (this.preview) {
            this.closePreview();
        } else if (this.confirmation) {
            this.confirmation = null;
        } else if (this.sectionModal) {
            this.closeSectionModal();
        } else {
            this.addMenuOpen = false;
            this.openSectionMenu = null;
        }
    },

    toggleSection(section) {
        this.collapsedSections[section.stableId] = !this.collapsedSections[section.stableId];
        localStorage.setItem('bec-survey-collapsed-sections', JSON.stringify(this.collapsedSections));
    },

    isSectionCollapsed(section) {
        return Boolean(this.collapsedSections[section.stableId]);
    },

    beginQuestionDrag(event, question, section) {
        if (event.button !== 0) {
            return;
        }

        event.preventDefault();
        this.startPointerDrag(event, {
            kind: 'question',
            stableId: question.stableId,
            sourceSection: section.stableId,
            originalSections: clone(this.state.sections),
            targetSection: section.stableId,
            targetIndex: section.questions.findIndex((item) => item.stableId === question.stableId),
        }, question.prompt);
    },

    beginSectionDrag(event, section) {
        if (event.button !== 0 || this.selectedSection !== 'all') {
            return;
        }

        event.preventDefault();
        this.startPointerDrag(event, {
            kind: 'section',
            stableId: section.stableId,
            originalSections: clone(this.state.sections),
            targetIndex: this.state.sections.findIndex((item) => item.stableId === section.stableId),
        }, section.title);
    },

    beginOptionDrag(event, option, index) {
        if (event.button !== 0) {
            return;
        }

        event.preventDefault();
        this.startPointerDrag(event, {
            kind: 'option',
            clientId: option.clientId,
            originalOptions: clone(this.editor.options),
            targetIndex: index,
        }, option.label || `Option ${index + 1}`);
    },

    startPointerDrag(event, drag, label) {
        const ghost = document.createElement('div');
        ghost.className = 'survey-drag-ghost';
        ghost.textContent = label;
        document.body.appendChild(ghost);
        this.drag = { ...drag, ghost };
        this.updateGhostPosition(event);
        document.body.classList.add('survey-is-dragging');
        this.pointerMoveHandler = (moveEvent) => this.handlePointerMove(moveEvent);
        this.pointerUpHandler = (upEvent) => this.finishPointerDrag(upEvent);
        window.addEventListener('pointermove', this.pointerMoveHandler, { passive: false });
        window.addEventListener('pointerup', this.pointerUpHandler, { once: true });
        window.addEventListener('pointercancel', this.pointerUpHandler, { once: true });
    },

    handlePointerMove(event) {
        if (!this.drag) {
            return;
        }

        event.preventDefault();
        this.updateGhostPosition(event);
        const target = document.elementFromPoint(event.clientX, event.clientY);

        if (this.drag.kind === 'question') {
            const list = target?.closest('[data-question-list]');

            if (!list) {
                return;
            }

            const rows = [...list.querySelectorAll('[data-question-row]')];
            let targetIndex = rows.length;

            rows.some((row, index) => {
                if (event.clientY < row.getBoundingClientRect().top + (row.getBoundingClientRect().height / 2)) {
                    targetIndex = index;

                    return true;
                }

                return false;
            });
            this.drag.targetSection = list.dataset.questionList;
            this.drag.targetIndex = targetIndex;
        } else if (this.drag.kind === 'section') {
            const cards = [...this.$root.querySelectorAll('[data-section-card]')];
            let targetIndex = cards.length - 1;

            cards.some((card, index) => {
                if (event.clientY < card.getBoundingClientRect().top + (card.getBoundingClientRect().height / 2)) {
                    targetIndex = index;

                    return true;
                }

                return false;
            });
            this.drag.targetIndex = Math.max(0, targetIndex);
        } else if (this.drag.kind === 'option') {
            const rows = [...this.$root.querySelectorAll('[data-option-row]')];
            let targetIndex = rows.length - 1;

            rows.some((row, index) => {
                if (event.clientY < row.getBoundingClientRect().top + (row.getBoundingClientRect().height / 2)) {
                    targetIndex = index;

                    return true;
                }

                return false;
            });
            this.drag.targetIndex = Math.max(0, targetIndex);
        }
    },

    updateGhostPosition(event) {
        if (!this.drag?.ghost) {
            return;
        }

        this.drag.ghost.style.transform = `translate3d(${event.clientX + 16}px, ${event.clientY + 14}px, 0)`;
    },

    async finishPointerDrag() {
        if (!this.drag) {
            return;
        }

        const drag = this.drag;
        this.removePointerListeners();

        if (drag.kind === 'question') {
            const sourceSection = this.sectionByStableId(drag.sourceSection);
            const targetSection = this.sectionByStableId(drag.targetSection);
            const sourceIndex = sourceSection?.questions.findIndex((question) => question.stableId === drag.stableId) ?? -1;

            if (!sourceSection || !targetSection || sourceIndex < 0) {
                return;
            }

            const [question] = sourceSection.questions.splice(sourceIndex, 1);
            let targetIndex = drag.targetIndex;

            if (sourceSection === targetSection && sourceIndex < targetIndex) {
                targetIndex -= 1;
            }

            targetIndex = Math.max(0, Math.min(targetIndex, targetSection.questions.length));
            question.sectionStableId = targetSection.stableId;
            targetSection.questions.splice(targetIndex, 0, question);
            this.renumberLocalState();

            try {
                const data = await this.request(this.routes.questionOrder, 'PUT', {
                    groups: this.state.sections.map((section) => ({
                        section_stable_id: section.stableId,
                        question_stable_ids: section.questions.map((item) => item.stableId),
                    })),
                });
                this.applyState(data.state);
                this.toast(data.message);
            } catch (error) {
                this.state.sections = drag.originalSections;
                this.toast(`${error.message} The previous order was restored.`, 'error');
            }
        } else if (drag.kind === 'section') {
            const sourceIndex = this.state.sections.findIndex((section) => section.stableId === drag.stableId);
            const [section] = this.state.sections.splice(sourceIndex, 1);
            let targetIndex = drag.targetIndex;

            if (sourceIndex < targetIndex) {
                targetIndex -= 1;
            }

            this.state.sections.splice(Math.max(0, targetIndex), 0, section);
            this.renumberLocalState();

            try {
                const data = await this.request(this.routes.sectionOrder, 'PUT', {
                    section_stable_ids: this.state.sections.map((item) => item.stableId),
                });
                this.applyState(data.state);
                this.toast(data.message);
            } catch (error) {
                this.state.sections = drag.originalSections;
                this.toast(`${error.message} The previous order was restored.`, 'error');
            }
        } else if (drag.kind === 'option') {
            const sourceIndex = this.editor.options.findIndex((option) => option.clientId === drag.clientId);
            const [option] = this.editor.options.splice(sourceIndex, 1);
            let targetIndex = drag.targetIndex;

            if (sourceIndex < targetIndex) {
                targetIndex -= 1;
            }

            this.editor.options.splice(Math.max(0, targetIndex), 0, option);
        }
    },

    removePointerListeners() {
        if (this.pointerMoveHandler) {
            window.removeEventListener('pointermove', this.pointerMoveHandler);
        }

        if (this.pointerUpHandler) {
            window.removeEventListener('pointerup', this.pointerUpHandler);
            window.removeEventListener('pointercancel', this.pointerUpHandler);
        }

        this.drag?.ghost?.remove();
        document.body.classList.remove('survey-is-dragging');
        this.pointerMoveHandler = null;
        this.pointerUpHandler = null;
        this.drag = null;
    },

    questionDropClass(question, section) {
        if (this.drag?.kind !== 'question' || this.drag.targetSection !== section.stableId) {
            return '';
        }

        const index = section.questions.findIndex((item) => item.stableId === question.stableId);

        if (this.drag.targetIndex === index) {
            return 'survey-drop-before';
        }

        if (this.drag.targetIndex === section.questions.length && index === section.questions.length - 1) {
            return 'survey-drop-after';
        }

        return '';
    },

    sectionDropClass(section) {
        if (this.drag?.kind !== 'section') {
            return '';
        }

        return this.drag.targetIndex === this.state.sections.findIndex((item) => item.stableId === section.stableId)
            ? 'survey-section-drop-target'
            : '';
    },

    optionDropClass(index) {
        return this.drag?.kind === 'option' && this.drag.targetIndex === index ? 'survey-drop-before' : '';
    },

    renumberLocalState() {
        this.state.sections.forEach((section, sectionIndex) => {
            section.position = sectionIndex + 1;
            section.questionCount = section.questions.length;
            section.questions.forEach((question, questionIndex) => {
                question.position = questionIndex + 1;
                question.sectionStableId = section.stableId;
            });
        });
        this.state.totalQuestions = this.state.sections.reduce((total, section) => total + section.questions.length, 0);
    },

    applyState(nextState) {
        this.state = clone(nextState);

        if (this.selectedSection !== 'all' && !this.sectionByStableId(this.selectedSection)) {
            this.selectedSection = 'all';
        }
    },

    async request(url, method, body = null, collectErrors = false, errorTarget = 'question') {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body === null ? null : JSON.stringify(body),
        });
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (collectErrors && data.errors) {
                if (errorTarget === 'section' && this.sectionModal) {
                    this.sectionModal.errors = data.errors;
                } else {
                    this.formErrors = data.errors;
                }
            }

            const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;

            throw new Error(firstError || data.message || 'The request could not be completed.');
        }

        return data;
    },

    fieldError(field) {
        return this.formErrors[field]?.[0] ?? '';
    },

    toast(message, type = 'success') {
        const id = crypto.randomUUID();
        this.toasts.push({ id, message, type });
        window.setTimeout(() => {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        }, 4200);
    },
});
