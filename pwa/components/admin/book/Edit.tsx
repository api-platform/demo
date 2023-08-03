import { EditGuesser, type EditGuesserProps } from "@api-platform/admin";

import { Form } from "@/components/admin/book/Form";

export const Edit = (props: EditGuesserProps) => (
  <EditGuesser {...props} title="Edit book">
    <Form/>
  </EditGuesser>
);
