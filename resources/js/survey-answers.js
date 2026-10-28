const stringValue = (value) => value === null || value === undefined ? '' : String(value);

export const createSurveyAnswers = (questions = [], initialAnswers = {}) => Object.fromEntries(
    questions.map((question) => {
        const questionId = String(question.id);
        const initialAnswer = initialAnswers[questionId] ?? initialAnswers[question.id];

        if (question.type === 'multiple_choice') {
            return [
                questionId,
                Array.isArray(initialAnswer) ? initialAnswer.map(stringValue) : [],
            ];
        }

        if (question.type === 'grouped_choice') {
            const groupedAnswers = initialAnswer && typeof initialAnswer === 'object'
                ? initialAnswer
                : {};

            return [
                questionId,
                Object.fromEntries(
                    (question.groups ?? []).map((group) => [group, stringValue(groupedAnswers[group])]),
                ),
            ];
        }

        return [questionId, stringValue(initialAnswer)];
    }),
);
