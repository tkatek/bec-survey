const MOBILE_BREAKPOINT = 640;
const TABLET_BREAKPOINT = 900;

const createSectionStep = (section, sectionIndex, currentQuestionIndex, isComplete) => ({
    key: section.key ?? `section-${sectionIndex}`,
    type: 'section',
    sectionIndex,
    number: sectionIndex + 1,
    label: section.name,
    ariaLabel: `Section ${sectionIndex + 1}: ${section.name}`,
    isActive: !isComplete
        && currentQuestionIndex >= section.start
        && currentQuestionIndex <= section.end,
    isComplete: isComplete || currentQuestionIndex > section.end,
});

const createEllipsisStep = (position, hiddenCount, isComplete) => ({
    key: `ellipsis-${position}-${hiddenCount}`,
    type: 'ellipsis',
    label: 'More sections',
    ariaLabel: `${hiddenCount} ${position === 'before' ? 'earlier' : 'later'} ${hiddenCount === 1 ? 'section' : 'sections'}`,
    isActive: false,
    isComplete,
});

const activeSectionIndex = (sections, currentQuestionIndex) => {
    const activeIndex = sections.findIndex(
        (section) => currentQuestionIndex >= section.start && currentQuestionIndex <= section.end,
    );

    if (activeIndex !== -1) {
        return activeIndex;
    }

    return currentQuestionIndex > (sections.at(-1)?.end ?? 0) ? sections.length - 1 : 0;
};

const visibleSectionLimit = (sectionCount, viewportWidth) => {
    if (viewportWidth <= MOBILE_BREAKPOINT) {
        return Math.min(sectionCount, 3);
    }

    if (sectionCount > 8) {
        return viewportWidth <= TABLET_BREAKPOINT ? 5 : 6;
    }

    return sectionCount;
};

export const getSectionTrackDensity = (sectionCount, viewportWidth) => {
    if (viewportWidth <= MOBILE_BREAKPOINT) {
        return 'mobile';
    }

    if (sectionCount <= 4) {
        return 'comfortable';
    }

    if (sectionCount <= 6) {
        return 'compact';
    }

    return sectionCount <= 8 ? 'dense' : 'windowed';
};

export const buildVisibleSectionSteps = (
    sections,
    currentQuestionIndex,
    viewportWidth,
    isComplete = false,
) => {
    if (sections.length === 0) {
        return [];
    }

    const activeIndex = activeSectionIndex(sections, currentQuestionIndex);
    const sectionLimit = visibleSectionLimit(sections.length, viewportWidth);
    const windowStart = Math.min(
        Math.max(activeIndex - Math.floor(sectionLimit / 2), 0),
        sections.length - sectionLimit,
    );
    const windowEnd = windowStart + sectionLimit;
    const steps = [];

    if (windowStart > 0) {
        steps.push(createEllipsisStep('before', windowStart, true));
    }

    sections.slice(windowStart, windowEnd).forEach((section, offset) => {
        steps.push(createSectionStep(
            section,
            windowStart + offset,
            currentQuestionIndex,
            isComplete,
        ));
    });

    if (windowEnd < sections.length) {
        steps.push(createEllipsisStep('after', sections.length - windowEnd, isComplete));
    }

    return steps;
};
