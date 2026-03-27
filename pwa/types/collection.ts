export interface PagedCollection<T> {
  "@context"?: string;
  "@id"?: string;
  "@type"?: string;
  "member"?: T[];
  "search"?: object;
  "totalItems"?: number;
  "view"?: {
    "@id": string;
    "@type": string;
    "first"?: string;
    "last"?: string;
    "previous"?: string;
    "next"?: string;
  };
}

export const isPagedCollection = <T>(data: any): data is PagedCollection<T> =>
  "member" in data && Array.isArray(data["member"]);
