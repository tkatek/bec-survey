import assert from 'node:assert/strict';
import test from 'node:test';

import { createSurveyAnswers } from '../../resources/js/survey-answers.js';

const questions = [
    { id: 1, type: 'single_choice' },
    { id: 2, type: 'multiple_choice' },
    { id: 3, type: 'grouped_choice', groups: ['Preferred time', 'Time zone'] },
    { id: 4, type: 'long_text' },
];

test('creates empty values matching every survey question type', () => {
    assert.deepEqual(createSurveyAnswers(questions), {
        1: '',
        2: [],
        3: { 'Preferred time': '', 'Time zone': '' },
        4: '',
    });
});

test('restores server-side old input using the string values expected by Alpine', () => {
    assert.deepEqual(createSurveyAnswers(questions, {
        1: 11,
        2: [21, '22'],
        3: { 'Preferred time': 31, 'Time zone': '32' },
        4: 'More evening sessions.',
    }), {
        1: '11',
        2: ['21', '22'],
        3: { 'Preferred time': '31', 'Time zone': '32' },
        4: 'More evening sessions.',
    });
});

test('ignores malformed old input instead of breaking the survey', () => {
    assert.deepEqual(createSurveyAnswers(questions, {
        2: 'not-an-array',
        3: null,
    }), {
        1: '',
        2: [],
        3: { 'Preferred time': '', 'Time zone': '' },
        4: '',
    });
});
