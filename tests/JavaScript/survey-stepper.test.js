import assert from 'node:assert/strict';
import test from 'node:test';

import { buildVisibleSectionSteps, getSectionTrackDensity } from '../../resources/js/survey-stepper.js';

const createSections = (count) => Array.from({ length: count }, (_, index) => ({
    key: `section-${index + 1}`,
    name: `Section ${index + 1}`,
    start: index * 2,
    end: (index * 2) + 1,
}));

for (const sectionCount of [3, 4, 5, 6, 8]) {
    test(`desktop renders all ${sectionCount} sections`, () => {
        const steps = buildVisibleSectionSteps(createSections(sectionCount), 0, 1440);

        assert.equal(steps.length, sectionCount);
        assert.deepEqual(steps.map((step) => step.number), Array.from({ length: sectionCount }, (_, index) => index + 1));
        assert.equal(steps.some((step) => step.type === 'ellipsis'), false);
    });
}

test('desktop uses a centered sliding window for ten sections', () => {
    const steps = buildVisibleSectionSteps(createSections(10), 10, 1440);

    assert.deepEqual(steps.map((step) => step.type), [
        'ellipsis',
        'section',
        'section',
        'section',
        'section',
        'section',
        'section',
        'ellipsis',
    ]);
    assert.deepEqual(steps.filter((step) => step.type === 'section').map((step) => step.number), [3, 4, 5, 6, 7, 8]);
    assert.equal(steps.find((step) => step.isActive)?.number, 6);
});

test('mobile shows the current section with its nearest neighbors and ellipses', () => {
    const steps = buildVisibleSectionSteps(createSections(10), 10, 390);

    assert.deepEqual(steps.map((step) => step.type), ['ellipsis', 'section', 'section', 'section', 'ellipsis']);
    assert.deepEqual(steps.filter((step) => step.type === 'section').map((step) => step.number), [5, 6, 7]);
    assert.equal(steps.find((step) => step.isActive)?.number, 6);
});

test('active and completed section states follow question navigation', () => {
    const sections = createSections(6);
    const firstQuestionSteps = buildVisibleSectionSteps(sections, 0, 1440);
    const ninthQuestionSteps = buildVisibleSectionSteps(sections, 8, 1440);

    assert.equal(firstQuestionSteps.find((step) => step.isActive)?.number, 1);
    assert.equal(ninthQuestionSteps.find((step) => step.isActive)?.number, 5);
    assert.deepEqual(
        ninthQuestionSteps.filter((step) => step.isComplete).map((step) => step.number),
        [1, 2, 3, 4],
    );
});

test('density adapts to section count and mobile width', () => {
    assert.equal(getSectionTrackDensity(3, 1440), 'comfortable');
    assert.equal(getSectionTrackDensity(4, 1440), 'comfortable');
    assert.equal(getSectionTrackDensity(5, 1440), 'compact');
    assert.equal(getSectionTrackDensity(6, 1440), 'compact');
    assert.equal(getSectionTrackDensity(7, 1440), 'dense');
    assert.equal(getSectionTrackDensity(8, 1440), 'dense');
    assert.equal(getSectionTrackDensity(10, 1440), 'windowed');
    assert.equal(getSectionTrackDensity(10, 390), 'mobile');
});
