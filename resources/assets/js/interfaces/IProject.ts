export interface IProjectIdea {
    id: number
    title: string
    description: string
    ranking: number
    status: string
    prototypes: IPrototype[]
    created_at: string
    updated_at: string
}

export interface IPrototype {
    id: number
    status: string
    created_at: string
    updated_at: string
}

export interface IProjectDocument {
    id: number
    filename: string
    type: string
    status: string
}

export default interface IProject {
    id: number;
    name: string;
    description: string;
    stage: string;
    status: string;
    project_ideas: IProjectIdea[];
    project_documents: IProjectDocument[];
    created_at: string;
    updated_at: string;
}
