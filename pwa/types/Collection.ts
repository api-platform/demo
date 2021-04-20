export interface PagedCollection<T> {
  "@context"?: string;
  "@id"?: string;
  "@type"?: string;
  "hydra:firstPage"?: string;
  "hydra:itemsPerPage"?: number;
  "hydra:lastPage"?: string;
  "hydra:member"?: T[];
  "hydra:nextPage"?: string;
  "hydra:search"?: object;
  "hydra:totalItems"?: number;
}
