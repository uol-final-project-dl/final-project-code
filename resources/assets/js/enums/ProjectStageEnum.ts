// Project stages enum

export enum ProjectStageEnum {
    BRAINSTORMING = 'brainstorming',
    IDEATING = 'ideating',
    PROTOTYPING = 'prototyping',
    CODING = 'coding',
    ARCHIVED = 'archived',
}

export const getProjectStageLabel = (stage: ProjectStageEnum): string => {
    switch (stage) {
        case ProjectStageEnum.BRAINSTORMING:
            return 'Brainstorming';
        case ProjectStageEnum.IDEATING:
            return 'Ideating';
        case ProjectStageEnum.PROTOTYPING:
            return 'Prototyping';
        case ProjectStageEnum.CODING:
            return 'Coding';
        case ProjectStageEnum.ARCHIVED:
            return 'Archived';
        default:
            return 'Unknown Stage';
    }
}
