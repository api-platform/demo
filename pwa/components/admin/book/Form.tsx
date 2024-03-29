import { required } from "react-admin";

import { ConditionInput } from "./ConditionInput";
import { BookInput } from "./BookInput";

export const Form = () => (
  <>
    <BookInput source="book" validate={required()}/>
    <ConditionInput source="condition" validate={required()}/>
  </>
);
