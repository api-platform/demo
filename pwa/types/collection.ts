export interface PagedCollection<T> {
  "@context"?: string;
  "@id"?: string;
  "@type"?: string;
  "hydra:member"?: T[];
  "hydra:search"?: object;
  "hydra:totalItems"?: number;
  "hydra:view"?: {
    "@id": string;
    "@type": string;
    "hydra:first"?: string;
    "hydra:last"?: string;
    "hydra:previous"?: string;
    "hydra:next"?: string;
  };
}

export const isPagedCollection = <T>(data: any): data is PagedCollection<T> =>
  "hydra:member" in data && Array.isArray(data["hydra:member"]);
