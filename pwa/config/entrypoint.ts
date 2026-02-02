// @ts-expect-error Ignore Eslint error
export const ENTRYPOINT: string = typeof window === "undefined" ? process.env.NEXT_PUBLIC_ENTRYPOINT : window.origin;
