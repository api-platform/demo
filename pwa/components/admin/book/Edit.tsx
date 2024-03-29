import { EditGuesser, type EditGuesserProps } from "@api-platform/admin";
import { TopToolbar } from 'react-admin';

import { Form } from "./Form";
import { ShowButton } from "./ShowButton";

// @ts-ignore
const Actions = ({ data }) => (
  <TopToolbar>
    <ShowButton record={data} />
  </TopToolbar>
);
export const Edit = (props: EditGuesserProps) => (
  // @ts-ignore
  <EditGuesser {...props} title="Edit book" actions={<Actions/>}>
    <Form/>
  </EditGuesser>
);
