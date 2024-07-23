export interface CommandAction {
    action: string;
    title: string;
    fields?: { [key: string]: SpecialCommandField };
}
