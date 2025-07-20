export enum StatusEnum {
    REQUEST_DATA = 'request_data',
    QUEUED = 'queued',
    READY = 'ready',
    FAILED = 'failed',
}

export const getStatusLabel = (status: StatusEnum): string => {
    switch (status) {
        case StatusEnum.REQUEST_DATA:
            return 'Request Data';
        case StatusEnum.QUEUED:
            return 'Queued';
        case StatusEnum.READY:
            return 'Ready';
        case StatusEnum.FAILED:
            return 'Failed';
        default:
            return 'Unknown Status';
    }
}
