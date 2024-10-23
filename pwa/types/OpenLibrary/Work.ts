import { type Description } from "./Description";

export class Work {
  constructor(
    public description?: string | Description,
    public covers?: Array<number>,
  ) {
  }
}
