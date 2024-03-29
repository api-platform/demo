import { CreateGuesser, type CreateGuesserProps } from "@api-platform/admin";

import { Form } from "./Form";

export const Create = (props: CreateGuesserProps) => (
  <CreateGuesser {...props} title="Create book">
    <Form/>
  </CreateGuesser>
);
