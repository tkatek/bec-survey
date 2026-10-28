<x-app-layout>
    <div
        x-data="surveyQuestionManager(@js($surveyState), @js([
            'routes' => $surveyRoutes,
            'types' => $questionTypes,
            'icons' => $iconChoices,
            'tones' => $toneChoices,
        ]))"
        @keydown.escape.window="closeOnEscape"
        class="question-admin admin-secondary-page min-h-screen"
    >
        @include('conversation-room.partials.admin-icon-sprite')

        <div class="mx-auto w-full max-w-[1700px] p-4 sm:p-6 xl:p-8">
            <header class="flex flex-col gap-5 2xl:flex-row 2xl:items-end 2xl:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#6f82a8]">Survey Management</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.04em] text-[#071326] sm:text-3xl">Manage Survey Questions</h1>
                    <p class="mt-1.5 max-w-3xl text-sm font-medium leading-6 text-[#63749a]">Add, edit, remove and organize the questions in your Conversation Room feedback survey.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2.5 text-xs font-bold text-emerald-700">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span x-text="state.version.hasDraft ? `Version ${state.version.activeNumber} active · Draft ${state.version.number}` : `Survey is active · Version ${state.version.activeNumber}`"></span>
                    </span>
                    <a :href="routes.fullPreview" target="_blank" rel="noopener" class="admin-control inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-xs font-extrabold text-[#1357e8] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]">
                        Preview survey
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3h7v7M10 14 21 3" /><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5" /></svg>
                    </a>
                    <button x-show="state.version.hasDraft" x-cloak type="button" @click="askPublish" :disabled="publishing" class="inline-flex items-center gap-2 rounded-full bg-[#071326] px-4 py-2.5 text-xs font-extrabold text-white shadow-lg transition hover:bg-[#10214b] disabled:cursor-wait disabled:opacity-60 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-2">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0-12 4 4m-4-4L8 7" /><path d="M5 13v6h14v-6" /></svg>
                        Publish Changes
                    </button>
                </div>
            </header>

            <div class="mt-6 flex flex-col gap-3 2xl:flex-row 2xl:items-stretch 2xl:justify-between">
                <div class="admin-glass-panel survey-tabs-scroll min-w-0 overflow-x-auto rounded-2xl p-1">
                    <div class="flex min-w-max items-stretch">
                        <button type="button" @click="selectedSection = 'all'" :class="selectedSection === 'all' ? 'bg-[#1357e8] text-white shadow-md' : 'text-[#10214b] hover:bg-[#f6f9ff]'" class="rounded-xl px-5 py-3 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]">
                            <span class="block text-xs font-extrabold">All Questions (<span x-text="state.totalQuestions"></span>)</span>
                        </button>
                        <template x-for="(section, index) in state.sections" :key="section.stableId">
                            <button type="button" @click="selectedSection = section.stableId" :class="selectedSection === section.stableId ? 'bg-[#edf4ff] text-[#1357e8]' : 'text-[#10214b] hover:bg-[#f6f9ff]'" class="min-w-44 rounded-xl px-5 py-2.5 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]">
                                <span class="block text-[11px] font-extrabold" x-text="`Section ${index + 1}`"></span>
                                <span class="mt-0.5 block max-w-48 truncate text-[11px] font-semibold text-[#6d7fa4]" x-text="`${section.title} (${section.questionCount})`"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="relative shrink-0" @click.outside="addMenuOpen = false">
                    <button type="button" @click="addMenuOpen = !addMenuOpen" :aria-expanded="addMenuOpen.toString()" class="admin-primary-action flex h-full min-h-12 w-full items-center justify-center gap-3 rounded-xl px-6 py-3 text-sm font-extrabold text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-2 2xl:w-48">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" /></svg>
                        Add to survey
                        <svg class="ml-auto h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 10 5 5 5-5" /></svg>
                    </button>
                    <div x-show="addMenuOpen" x-cloak x-transition.origin.top.right class="admin-popover absolute right-0 z-30 mt-2 w-52 rounded-2xl border p-2">
                        <button type="button" @click="startNewQuestion()" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-bold text-[#10214b] hover:bg-[#edf4ff] hover:text-[#1357e8]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" /></svg>Add Question</button>
                        <button type="button" @click="openSectionEditor()" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-bold text-[#10214b] hover:bg-[#edf4ff] hover:text-[#1357e8]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h10" /></svg>Add Section</button>
                    </div>
                </div>
            </div>

            <div :class="editorOpen ? 'question-admin-workspace--editor-open' : ''" class="question-admin-workspace mt-4 grid min-w-0 gap-4">
                <main class="min-w-0 space-y-3" aria-label="Survey sections and questions">
                    <template x-for="(section, sectionIndex) in filteredSections" :key="section.stableId">
                        <article data-section-card :data-section-stable-id="section.stableId" :class="sectionDropClass(section)" class="question-section-card min-w-0 rounded-2xl border border-[#e0e8f3] bg-white shadow-[0_10px_28px_rgba(25,67,124,0.055)] transition">
                            <header class="flex min-w-0 items-center gap-3 px-4 py-3 sm:px-5">
                                <button type="button" @pointerdown="beginSectionDrag($event, section)" :disabled="selectedSection !== 'all'" title="Drag to reorder section" class="survey-drag-handle hidden h-9 w-6 shrink-0 cursor-grab touch-none place-items-center text-[#8a9aba] hover:text-[#1357e8] disabled:cursor-default disabled:opacity-25 sm:grid">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="7" r="1.5"/><circle cx="15" cy="7" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="17" r="1.5"/><circle cx="15" cy="17" r="1.5"/></svg>
                                </button>
                                <span :class="['bg-rose-50 text-rose-600', 'bg-emerald-50 text-emerald-600', 'bg-blue-50 text-blue-600', 'bg-amber-50 text-amber-600', 'bg-purple-50 text-purple-600'][section.position % 5]" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-sm font-extrabold" x-text="section.position"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="min-w-0 text-sm font-extrabold text-[#071326] sm:text-base" x-text="`Section ${section.position}: ${section.title}`"></h2>
                                        <span x-show="!section.isActive" class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">Hidden</span>
                                    </div>
                                    <p class="mt-0.5 line-clamp-2 text-xs font-medium leading-5 text-[#6a7ca0]" x-text="section.description || 'No section description.'"></p>
                                </div>
                                <span class="hidden shrink-0 text-xs font-bold text-[#53668d] sm:block"><span x-text="section.questionCount"></span> <span x-text="section.questionCount === 1 ? 'question' : 'questions'"></span></span>

                                <div class="relative" @click.outside="if (openSectionMenu === section.stableId) openSectionMenu = null">
                                    <button type="button" @click="openSectionMenu = openSectionMenu === section.stableId ? null : section.stableId" aria-label="Section actions" title="Section actions" class="grid h-9 w-9 place-items-center rounded-lg text-[#617397] hover:bg-[#edf4ff] hover:text-[#1357e8] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg></button>
                                    <div x-show="openSectionMenu === section.stableId" x-cloak x-transition.origin.top.right class="absolute right-0 z-30 mt-1 w-48 rounded-xl border border-[#dce5f2] bg-white p-1.5 text-xs font-bold text-[#33476b] shadow-[0_16px_38px_rgba(22,52,99,0.16)]">
                                        <button type="button" @click="openSectionEditor(section)" class="w-full rounded-lg px-3 py-2 text-left hover:bg-[#edf4ff] hover:text-[#1357e8]">Edit section</button>
                                        <button type="button" @click="moveSection(section, 'up')" :disabled="section.position === 1" class="w-full rounded-lg px-3 py-2 text-left hover:bg-[#edf4ff] disabled:opacity-40">Move up</button>
                                        <button type="button" @click="moveSection(section, 'down')" :disabled="section.position === state.sections.length" class="w-full rounded-lg px-3 py-2 text-left hover:bg-[#edf4ff] disabled:opacity-40">Move down</button>
                                        <button type="button" @click="askDeleteSection(section)" class="w-full rounded-lg px-3 py-2 text-left text-rose-600 hover:bg-rose-50">Archive section</button>
                                    </div>
                                </div>

                                <button type="button" @click="toggleSection(section)" :aria-expanded="(!isSectionCollapsed(section)).toString()" :aria-label="isSectionCollapsed(section) ? 'Expand section' : 'Collapse section'" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-[#53668d] hover:bg-[#edf4ff] hover:text-[#1357e8] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]">
                                    <svg :class="isSectionCollapsed(section) ? '' : 'rotate-180'" class="h-4 w-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="m7 10 5 5 5-5" /></svg>
                                </button>
                            </header>

                            <div x-show="!isSectionCollapsed(section)" x-transition.opacity.duration.150ms class="border-t border-[#edf1f7]">
                                <div x-bind:data-question-list="section.stableId" :class="drag?.kind === 'question' && drag.targetSection === section.stableId ? 'bg-[#f6f9ff]' : ''" class="min-h-14 transition-colors">
                                    <template x-if="section.questions.length === 0">
                                        <div class="grid min-h-28 place-items-center gap-2 p-5 text-center">
                                            <p class="text-sm font-bold text-[#63749a]">No questions in this section yet.</p>
                                            <button type="button" @click="startNewQuestion(section.stableId)" class="text-xs font-extrabold text-[#1357e8] hover:underline">Add Question</button>
                                        </div>
                                    </template>

                                    <template x-for="question in section.questions" :key="question.stableId">
                                        <div data-question-row :data-question-stable-id="question.stableId" :class="questionDropClass(question, section)" class="question-row group relative grid min-h-14 min-w-0 grid-cols-[auto_auto_minmax(0,1fr)_auto] items-center gap-2 border-b border-[#edf1f7] px-3 py-2.5 last:border-b-0 sm:gap-3 sm:px-4">
                                            <div class="flex shrink-0 items-center">
                                                <button type="button" @pointerdown="beginQuestionDrag($event, question, section)" title="Drag to reorder question" aria-label="Drag to reorder question" class="survey-drag-handle grid h-8 w-7 cursor-grab touch-none place-items-center text-[#8a9aba] hover:text-[#1357e8]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="7" r="1.5"/><circle cx="15" cy="7" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="17" r="1.5"/><circle cx="15" cy="17" r="1.5"/></svg></button>
                                                <div class="hidden flex-col sm:flex">
                                                    <button type="button" @click="moveQuestion(question, 'up')" :disabled="question.position === 1" title="Move question up" aria-label="Move question up" class="h-4 text-[#9aa9c2] hover:text-[#1357e8] disabled:opacity-25"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 14 5-5 5 5" /></svg></button>
                                                    <button type="button" @click="moveQuestion(question, 'down')" :disabled="question.position === section.questions.length" title="Move question down" aria-label="Move question down" class="h-4 text-[#9aa9c2] hover:text-[#1357e8] disabled:opacity-25"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 10 5 5 5-5" /></svg></button>
                                                </div>
                                            </div>
                                            <span class="grid h-8 min-w-8 shrink-0 place-items-center rounded-lg bg-[#f0f4fa] px-1 text-xs font-extrabold text-[#10214b]" x-text="questionNumber(question.stableId)"></span>
                                            <div class="min-w-0">
                                                <div class="flex min-w-0 flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                                                    <p class="min-w-0 whitespace-normal text-xs font-semibold leading-5 text-[#10214b] sm:text-[13px]" x-text="question.prompt"></p>
                                                    <span :class="{
                                                        'bg-purple-50 text-purple-600': question.type === 'single_choice' || question.type === 'grouped_choice',
                                                        'bg-emerald-50 text-emerald-600': question.type === 'multiple_choice',
                                                        'bg-slate-100 text-slate-600': question.type === 'long_text',
                                                    }" class="w-fit shrink-0 rounded-full px-2.5 py-1 text-[10px] font-extrabold" x-text="questionTypeLabel(question.type)"></span>
                                                </div>
                                                <span x-show="!question.isActive" class="mt-1 inline-flex text-[10px] font-bold text-slate-400">Hidden from active survey</span>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-0.5">
                                                <button type="button" @click="editQuestion(question)" title="Edit question" aria-label="Edit question" class="question-action"><svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10Z"/><path d="m14 7 3 3"/></svg></button>
                                                <button type="button" @click="askDuplicateQuestion(question)" title="Duplicate question" aria-label="Duplicate question" class="question-action"><svg viewBox="0 0 24 24"><rect x="8" y="8" width="11" height="11" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg></button>
                                                <button type="button" @click="previewQuestion(question)" title="Preview student view" aria-label="Preview student view" class="question-action text-[#1357e8]"><svg viewBox="0 0 24 24"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></button>
                                                <button type="button" @click="askDeleteQuestion(question)" title="Delete question" aria-label="Delete question" class="question-action text-rose-500 hover:bg-rose-50 hover:text-rose-600"><svg viewBox="0 0 24 24"><path d="M4 7h16M9 7V4h6v3m3 0-1 14H7L6 7m4 4v6m4-6v6"/></svg></button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </article>
                    </template>
                </main>

                <aside x-show="editorOpen" x-cloak :class="editorOpen ? 'question-editor-open' : ''" class="question-editor-shell" aria-label="Question editor">
                    <div class="question-editor-card flex min-h-0 flex-col overflow-hidden rounded-2xl border">
                        <div class="flex items-center justify-between gap-3 px-5 pb-3 pt-5">
                            <h2 class="text-lg font-extrabold tracking-[-0.03em] text-[#071326]" x-text="editorTitle"></h2>
                            <button type="button" @click="cancelEditor" aria-label="Close question editor" class="grid h-9 w-9 place-items-center rounded-lg text-[#607296] transition hover:bg-[#edf4ff] hover:text-[#1357e8] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg></button>
                        </div>

                        <div class="mx-5 grid grid-cols-2 rounded-xl bg-[#f1f5fb] p-1">
                            <button type="button" @click="editorTab = 'basic'" :class="editorTab === 'basic' ? 'bg-white text-[#1357e8] shadow-sm' : 'text-[#53668d]'" class="rounded-lg px-3 py-2.5 text-xs font-extrabold transition">Basic Information</button>
                            <button type="button" @click="if (editor.type !== 'long_text') editorTab = 'options'" :disabled="editor.type === 'long_text'" :class="editorTab === 'options' ? 'bg-white text-[#1357e8] shadow-sm' : 'text-[#53668d]'" class="rounded-lg px-3 py-2.5 text-xs font-extrabold transition disabled:cursor-not-allowed disabled:opacity-50">Answer Options</button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-5">
                            <div x-show="editorTab === 'basic'" class="grid gap-5">
                                <label class="grid gap-2 text-xs font-extrabold text-[#10214b]">
                                    Question Text
                                    <textarea x-ref="questionText" x-model="editor.prompt" maxlength="500" rows="4" placeholder="Enter your question text…" :class="fieldError('prompt') ? 'border-rose-400' : 'border-[#cdd9ea]'" class="resize-y rounded-xl px-3.5 py-3 text-sm font-semibold leading-6 text-[#10214b] placeholder:text-[#9aabc6] focus:border-[#1357e8] focus:ring-[#1357e8]"></textarea>
                                    <span x-show="fieldError('prompt')" x-text="fieldError('prompt')" class="text-[11px] font-semibold text-rose-600"></span>
                                </label>

                                <fieldset class="grid gap-2">
                                    <legend class="text-xs font-extrabold text-[#10214b]">Question Type</legend>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" @click="setQuestionType('single_choice')" :class="editor.type === 'single_choice' ? 'border-[#1357e8] bg-[#edf4ff] text-[#1357e8]' : 'border-[#d6e0ee] bg-white text-[#405277]'" class="grid min-h-20 place-items-center gap-1 rounded-xl border p-2 text-[10px] font-extrabold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/></svg>Single Choice</button>
                                        <button type="button" @click="setQuestionType('multiple_choice')" :class="editor.type === 'multiple_choice' ? 'border-[#1357e8] bg-[#edf4ff] text-[#1357e8]' : 'border-[#d6e0ee] bg-white text-[#405277]'" class="grid min-h-20 place-items-center gap-1 rounded-xl border p-2 text-[10px] font-extrabold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="5" width="14" height="14" rx="2"/><path d="m9 12 2 2 4-5"/></svg>Multiple Choice</button>
                                        <button type="button" @click="setQuestionType('long_text')" :class="editor.type === 'long_text' ? 'border-[#1357e8] bg-[#edf4ff] text-[#1357e8]' : 'border-[#d6e0ee] bg-white text-[#405277]'" class="grid min-h-20 place-items-center gap-1 rounded-xl border p-2 text-[10px] font-extrabold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 7h14M5 12h14M5 17h9"/></svg>Open-ended Text</button>
                                    </div>
                                    <button x-show="editor.type === 'grouped_choice'" type="button" class="rounded-xl border border-[#1357e8] bg-[#edf4ff] px-3 py-2 text-left text-[10px] font-extrabold text-[#1357e8]">Grouped choice (existing advanced question)</button>
                                    <span x-show="fieldError('type')" x-text="fieldError('type')" class="text-[11px] font-semibold text-rose-600"></span>
                                </fieldset>

                                <div class="grid grid-cols-[minmax(0,1fr)_7rem] gap-3">
                                    <label class="grid gap-2 text-xs font-extrabold text-[#10214b]">Section
                                        <select x-model="editor.sectionStableId" @change="editor.position = positionOptions.length" class="min-w-0 rounded-xl border-[#cdd9ea] px-3 py-2.5 text-xs font-bold focus:border-[#1357e8] focus:ring-[#1357e8]">
                                            <template x-for="section in state.sections" :key="section.stableId"><option :value="section.stableId" x-text="section.title"></option></template>
                                        </select>
                                    </label>
                                    <label class="grid gap-2 text-xs font-extrabold text-[#10214b]">Order
                                        <select x-model.number="editor.position" class="rounded-xl border-[#cdd9ea] px-3 py-2.5 text-xs font-bold focus:border-[#1357e8] focus:ring-[#1357e8]">
                                            <template x-for="position in positionOptions" :key="position"><option :value="position" x-text="position"></option></template>
                                        </select>
                                    </label>
                                </div>

                                <label class="grid gap-2 text-xs font-extrabold text-[#10214b]">Optional helper text
                                    <input x-model="editor.helpText" maxlength="255" placeholder="Tell students how to answer…" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]">
                                    <span x-show="fieldError('help_text')" x-text="fieldError('help_text')" class="text-[11px] font-semibold text-rose-600"></span>
                                </label>

                                <label x-show="editor.type === 'multiple_choice'" class="grid gap-2 text-xs font-extrabold text-[#10214b]">Maximum selections
                                    <input type="number" x-model.number="editor.maxSelections" min="1" max="20" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-bold focus:border-[#1357e8] focus:ring-[#1357e8]">
                                    <span x-show="fieldError('max_selections')" x-text="fieldError('max_selections')" class="text-[11px] font-semibold text-rose-600"></span>
                                </label>

                                <div class="grid gap-3 border-t border-[#edf1f7] pt-4">
                                    <label class="flex cursor-pointer items-center justify-between gap-4">
                                        <span><strong class="block text-xs text-[#10214b]">Required Question</strong><small class="mt-1 block text-[11px] text-[#7182a3]">Students must answer this question.</small></span>
                                        <button type="button" role="switch" :aria-checked="editor.isRequired.toString()" @click="editor.isRequired = !editor.isRequired" :class="editor.isRequired ? 'bg-[#1357e8]' : 'bg-slate-300'" class="relative h-7 w-12 rounded-full transition"><span :class="editor.isRequired ? 'translate-x-6' : 'translate-x-1'" class="absolute left-0 top-1 h-5 w-5 rounded-full bg-white shadow transition"></span></button>
                                    </label>
                                    <label class="flex cursor-pointer items-center justify-between gap-4">
                                        <span><strong class="block text-xs text-[#10214b]">Show in Active Survey</strong><small class="mt-1 block text-[11px] text-[#7182a3]">Visible after draft changes are published.</small></span>
                                        <button type="button" role="switch" :aria-checked="editor.isActive.toString()" @click="editor.isActive = !editor.isActive" :class="editor.isActive ? 'bg-[#1357e8]' : 'bg-slate-300'" class="relative h-7 w-12 rounded-full transition"><span :class="editor.isActive ? 'translate-x-6' : 'translate-x-1'" class="absolute left-0 top-1 h-5 w-5 rounded-full bg-white shadow transition"></span></button>
                                    </label>
                                </div>
                            </div>

                            <div x-show="editorTab === 'options'" class="grid gap-4">
                                <div>
                                    <h3 class="text-sm font-extrabold text-[#071326]">Answer Options</h3>
                                    <p class="mt-1 text-xs leading-5 text-[#6a7ca0]">Drag options into order, edit their labels, and choose a recognizable icon.</p>
                                </div>
                                <div class="grid gap-2.5">
                                    <template x-for="(option, optionIndex) in editor.options" :key="option.clientId">
                                        <div data-option-row :class="optionDropClass(optionIndex)" class="relative rounded-xl border border-[#dce5f2] bg-[#f8faff] p-3">
                                            <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-start gap-2">
                                                <button type="button" @pointerdown="beginOptionDrag($event, option, optionIndex)" title="Drag to reorder option" aria-label="Drag to reorder option" class="survey-drag-handle mt-1 grid h-8 w-7 cursor-grab touch-none place-items-center text-[#8999b7]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="7" r="1.5"/><circle cx="15" cy="7" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="17" r="1.5"/><circle cx="15" cy="17" r="1.5"/></svg></button>
                                                <label class="grid gap-1 text-[10px] font-extrabold text-[#53668d]">Option text
                                                    <input x-model="option.label" maxlength="255" :class="optionError(optionIndex, 'label') ? 'border-rose-400' : 'border-[#cdd9ea]'" class="min-w-0 rounded-lg px-3 py-2 text-xs font-bold text-[#10214b] focus:border-[#1357e8] focus:ring-[#1357e8]">
                                                    <span x-show="optionError(optionIndex, 'label')" x-text="optionError(optionIndex, 'label')" class="text-[10px] text-rose-600"></span>
                                                </label>
                                                <button type="button" @click="removeOption(optionIndex)" title="Delete option" aria-label="Delete option" class="mt-5 grid h-8 w-8 place-items-center rounded-lg text-rose-500 hover:bg-rose-50"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M9 7V4h6v3m3 0-1 14H7L6 7m4 4v6m4-6v6"/></svg></button>
                                            </div>
                                            <div class="mt-2 grid grid-cols-2 gap-2 pl-9">
                                                <label class="grid gap-1 text-[10px] font-extrabold text-[#53668d]">Icon
                                                    <select x-model="option.icon" class="min-w-0 rounded-lg border-[#cdd9ea] px-2 py-2 text-[11px] font-bold focus:border-[#1357e8] focus:ring-[#1357e8]">@foreach ($iconChoices as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                                                </label>
                                                <label class="grid gap-1 text-[10px] font-extrabold text-[#53668d]">Color
                                                    <select x-model="option.tone" class="min-w-0 rounded-lg border-[#cdd9ea] px-2 py-2 text-[11px] font-bold focus:border-[#1357e8] focus:ring-[#1357e8]">@foreach ($toneChoices as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                                                </label>
                                            </div>
                                            <label x-show="editor.type === 'grouped_choice'" class="mt-2 grid gap-1 pl-9 text-[10px] font-extrabold text-[#53668d]">Group name<input x-model="option.groupName" class="rounded-lg border-[#cdd9ea] px-3 py-2 text-xs font-bold focus:border-[#1357e8] focus:ring-[#1357e8]"></label>
                                            <div class="mt-2 flex gap-1 pl-9 sm:hidden"><button type="button" @click="moveOption(optionIndex, 'up')" class="rounded-lg border border-[#dce5f2] px-2 py-1 text-[10px] font-bold">Move up</button><button type="button" @click="moveOption(optionIndex, 'down')" class="rounded-lg border border-[#dce5f2] px-2 py-1 text-[10px] font-bold">Move down</button></div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="addOption" class="inline-flex items-center justify-center gap-2 rounded-xl border border-dashed border-[#9db4d6] bg-white px-4 py-3 text-xs font-extrabold text-[#1357e8] hover:bg-[#edf4ff]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>Add Option</button>
                                <span x-show="fieldError('options')" x-text="fieldError('options')" class="text-[11px] font-semibold text-rose-600"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-[auto_auto_minmax(0,1fr)] gap-2 border-t border-[#e7edf6] bg-white px-5 py-4">
                            <button type="button" @click="cancelEditor" class="rounded-xl border border-[#ced9e9] px-3 py-2.5 text-xs font-extrabold text-[#33476b] hover:bg-slate-50">Cancel</button>
                            <button type="button" @click="previewQuestion()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#ced9e9] px-3 py-2.5 text-xs font-extrabold text-[#1357e8] hover:bg-[#edf4ff]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>Preview</button>
                            <button type="button" @click="submitQuestion" :disabled="saving" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1357e8] px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-blue-100 hover:bg-[#0b43be] disabled:cursor-wait disabled:opacity-60"><svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" class="opacity-25"/><path d="M21 12a9 9 0 0 0-9-9"/></svg><span x-text="saving ? 'Saving…' : (editorMode === 'edit' ? 'Save Changes' : 'Save Question')"></span></button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <button x-show="!editorOpen" x-cloak type="button" @click="editorOpen = true" class="fixed bottom-5 right-5 z-30 inline-flex items-center gap-2 rounded-full bg-[#1357e8] px-5 py-3 text-sm font-extrabold text-white shadow-xl xl:hidden"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>Open Editor</button>

        <div x-show="preview" x-cloak class="fixed inset-0 z-[80] grid place-items-center bg-[#071326]/55 p-3 backdrop-blur-sm sm:p-6" @click.self="closePreview">
            <section x-show="preview" x-transition @keydown="trapFocus" role="dialog" aria-modal="true" aria-labelledby="preview-question-title" :class="previewDevice === 'mobile' ? 'admin-question-preview--mobile max-w-[390px]' : 'max-w-2xl'" class="admin-question-preview max-h-[calc(100dvh-1.5rem)] w-full overflow-y-auto rounded-2xl bg-white shadow-[0_30px_80px_rgba(7,19,38,0.28)] sm:max-h-[calc(100dvh-3rem)]">
                <header class="flex items-start justify-between gap-4 border-b border-[#e4ebf5] px-5 py-4">
                    <div class="min-w-0">
                        <h2 id="preview-question-title" class="flex items-center gap-2 text-lg font-extrabold text-[#071326]"><span class="grid h-8 w-8 place-items-center rounded-lg bg-[#edf4ff] text-[#1357e8]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></span>Preview Question</h2>
                        <p class="mt-1 text-xs leading-5 text-[#63749a]">This is how the question will appear to students in the survey.</p>
                    </div>
                    <button x-ref="previewClose" type="button" @click="closePreview" aria-label="Close preview" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-[#53668d] hover:bg-[#edf4ff]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
                </header>
                <div class="flex justify-end gap-1 px-5 pt-3">
                    <button type="button" @click="previewDevice = 'desktop'" :aria-pressed="(previewDevice === 'desktop').toString()" :class="previewDevice === 'desktop' ? 'bg-[#1357e8] text-white' : 'bg-[#f1f5fb] text-[#53668d]'" class="rounded-lg px-3 py-1.5 text-[10px] font-extrabold">Desktop</button>
                    <button type="button" @click="previewDevice = 'mobile'" :aria-pressed="(previewDevice === 'mobile').toString()" :class="previewDevice === 'mobile' ? 'bg-[#1357e8] text-white' : 'bg-[#f1f5fb] text-[#53668d]'" class="rounded-lg px-3 py-1.5 text-[10px] font-extrabold">Mobile</button>
                </div>
                <div class="survey-progress px-5">
                    <div class="survey-progress-copy"><span>Question <strong x-text="preview?.number"></strong> of <span x-text="Math.max(state.totalQuestions, preview?.number || 1)"></span></span><span><strong x-text="previewProgress()"></strong>% complete</span></div>
                    <div class="survey-progress-track"><span :style="`width: ${previewProgress()}%`"></span></div>
                </div>
                <div class="survey-stage admin-preview-stage px-5 pb-6 pt-3">
                    <div class="survey-step">
                        <div class="survey-question-heading">
                            <p x-text="preview?.sectionTitle"></p>
                            <div><span class="survey-question-number" x-text="String(preview?.number || 1).padStart(2, '0')"></span><div><h2 x-text="preview?.prompt"></h2><span x-show="preview?.helpText" x-text="preview?.helpText"></span></div></div>
                        </div>

                        <template x-if="preview?.type === 'long_text'">
                            <div class="survey-text-response"><label>Student answer</label><textarea rows="5" maxlength="1000" placeholder="Share your answer here…"></textarea><span>0 / 1000</span></div>
                        </template>

                        <template x-if="preview?.type === 'grouped_choice'">
                            <div class="survey-split-fields">
                                <template x-for="group in previewGroups()" :key="group">
                                    <fieldset class="survey-field-group"><legend x-text="group"></legend><div class="survey-options survey-options--compact">
                                        <template x-for="option in preview.options.filter((item) => item.groupName === group)" :key="option.stableId || option.clientId || option.id">
                                            <label class="survey-option"><input class="survey-option-input" type="radio" :name="`preview-${group}`" :checked="previewOptionSelected(option)" @change="selectPreviewOption(option)"><span class="survey-option-marker" :data-tone="option.tone"><svg viewBox="0 0 24 24"><use :href="`#survey-icon-${option.icon}`"></use></svg></span><span class="survey-option-copy" x-text="option.label || 'Untitled option'"></span><span class="survey-option-control"></span></label>
                                        </template>
                                    </div></fieldset>
                                </template>
                            </div>
                        </template>

                        <template x-if="preview?.type !== 'long_text' && preview?.type !== 'grouped_choice'">
                            <div>
                                <div x-show="preview?.type === 'multiple_choice' && preview?.maxSelections" class="survey-selection-count"><span x-text="previewSelections.length"></span> of <span x-text="preview?.maxSelections"></span> selected</div>
                                <fieldset :class="preview?.options?.length > 7 ? 'survey-options survey-options--compact' : 'survey-options'">
                                    <legend class="sr-only" x-text="preview?.prompt"></legend>
                                    <template x-for="option in preview?.options || []" :key="option.stableId || option.clientId || option.id">
                                        <label :class="preview?.type === 'multiple_choice' ? 'survey-option survey-option--check' : 'survey-option'"><input class="survey-option-input" :type="preview?.type === 'multiple_choice' ? 'checkbox' : 'radio'" name="preview-answer" :checked="previewOptionSelected(option)" @change="selectPreviewOption(option)"><span class="survey-option-marker" :data-tone="option.tone"><svg viewBox="0 0 24 24"><use :href="`#survey-icon-${option.icon}`"></use></svg></span><span class="survey-option-copy" x-text="option.label || 'Untitled option'"></span><span class="survey-option-control"></span></label>
                                    </template>
                                </fieldset>
                                <p x-show="(preview?.options || []).length === 0" class="mt-5 rounded-xl bg-slate-50 p-4 text-center text-xs font-semibold text-slate-500">Add at least two answer options to complete this preview.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </section>
        </div>

        <div x-show="sectionModal" x-cloak class="fixed inset-0 z-[75] grid place-items-center bg-[#071326]/50 p-4 backdrop-blur-sm" @click.self="closeSectionModal">
            <template x-if="sectionModal">
            <form @submit.prevent="saveSection" @keydown="trapFocus" class="w-full max-w-lg rounded-2xl bg-white p-5 shadow-[0_28px_75px_rgba(7,19,38,0.25)]" role="dialog" aria-modal="true" aria-labelledby="section-modal-title">
                <div class="flex items-center justify-between gap-3"><h2 id="section-modal-title" class="text-lg font-extrabold text-[#071326]" x-text="sectionModal?.mode === 'edit' ? 'Edit Section' : 'Add Section'"></h2><button type="button" @click="closeSectionModal" aria-label="Close section editor" class="grid h-9 w-9 place-items-center rounded-lg text-[#53668d] hover:bg-[#edf4ff]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div>
                <div class="mt-5 grid gap-4">
                    <label class="grid gap-2 text-xs font-extrabold text-[#10214b]">Section title<input x-ref="sectionTitle" x-model="sectionModal.title" maxlength="120" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]"><span x-show="sectionModal?.errors?.title" x-text="sectionModal?.errors?.title?.[0]" class="text-[11px] text-rose-600"></span></label>
                    <label class="grid gap-2 text-xs font-extrabold text-[#10214b]">Description <span class="font-medium text-[#7c8dab]">(optional)</span><textarea x-model="sectionModal.description" rows="3" maxlength="255" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]"></textarea></label>
                    <div class="grid grid-cols-2 gap-3"><label class="grid gap-2 text-xs font-extrabold text-[#10214b]">Position<input type="number" x-model.number="sectionModal.position" min="1" :max="state.sections.length + (sectionModal?.mode === 'create' ? 1 : 0)" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-bold focus:border-[#1357e8] focus:ring-[#1357e8]"></label><label class="flex items-center justify-between gap-3 rounded-xl border border-[#dce5f2] p-3 text-xs font-extrabold text-[#10214b]">Active<button type="button" role="switch" :aria-checked="sectionModal?.isActive?.toString()" @click="sectionModal.isActive = !sectionModal.isActive" :class="sectionModal?.isActive ? 'bg-[#1357e8]' : 'bg-slate-300'" class="relative h-7 w-12 rounded-full"><span :class="sectionModal?.isActive ? 'translate-x-6' : 'translate-x-1'" class="absolute left-0 top-1 h-5 w-5 rounded-full bg-white shadow transition"></span></button></label></div>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="closeSectionModal" class="rounded-xl border border-[#cdd9ea] px-4 py-2.5 text-xs font-extrabold text-[#33476b]">Cancel</button><button type="submit" :disabled="sectionModal?.saving" class="rounded-xl bg-[#1357e8] px-5 py-2.5 text-xs font-extrabold text-white disabled:opacity-60" x-text="sectionModal?.saving ? 'Saving…' : 'Save Section'"></button></div>
            </form>
            </template>
        </div>

        <div x-show="confirmation" x-cloak class="fixed inset-0 z-[90] grid place-items-center bg-[#071326]/55 p-4 backdrop-blur-sm" @click.self="confirmation = null">
            <section x-show="confirmation" @keydown="trapFocus" role="alertdialog" aria-modal="true" aria-labelledby="confirmation-title" class="w-full max-w-md rounded-2xl bg-white p-5 shadow-[0_28px_75px_rgba(7,19,38,0.28)]">
                <span :class="confirmation?.danger ? 'bg-rose-50 text-rose-600' : 'bg-[#edf4ff] text-[#1357e8]'" class="grid h-11 w-11 place-items-center rounded-xl"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01"/><path d="M10.3 3.7 2.4 17.4A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.6L13.7 3.7a2 2 0 0 0-3.4 0Z"/></svg></span>
                <h2 id="confirmation-title" class="mt-4 text-lg font-extrabold text-[#071326]" x-text="confirmation?.title"></h2>
                <p class="mt-2 text-sm leading-6 text-[#63749a]" x-text="confirmation?.message"></p>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="confirmation = null" class="rounded-xl border border-[#cdd9ea] px-4 py-2.5 text-xs font-extrabold text-[#33476b]">Cancel</button><button type="button" @click="confirmation?.action()" :class="confirmation?.danger ? 'bg-rose-600 hover:bg-rose-700' : 'bg-[#1357e8] hover:bg-[#0b43be]'" class="rounded-xl px-5 py-2.5 text-xs font-extrabold text-white" x-text="confirmation?.confirmLabel"></button></div>
            </section>
        </div>

        <div class="pointer-events-none fixed right-4 top-4 z-[110] grid w-[min(24rem,calc(100vw-2rem))] gap-2" aria-live="polite">
            <template x-for="toastItem in toasts" :key="toastItem.id"><div x-transition :class="toastItem.type === 'error' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-white text-emerald-700'" class="pointer-events-auto rounded-xl border px-4 py-3 text-xs font-bold shadow-[0_14px_34px_rgba(22,52,99,0.15)]" x-text="toastItem.message"></div></template>
        </div>
    </div>
</x-app-layout>
