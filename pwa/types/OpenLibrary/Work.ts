import { type Description } from "@/types/OpenLibrary/Description";

export class Work {
  constructor(
    public description?: string | Description,
    public covers?: Array<number>,
  ) {
  }
}
