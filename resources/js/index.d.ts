import routeType from "ziggy-js";

declare global {
    const route: typeof routeType;

    declare module "*.png" {
        const value: any;
        export = value;
    }
}
