import { required } from "react-admin";

import { ConditionInput } from "@/components/admin/book/ConditionInput";
import { BookInput } from "@/components/admin/book/BookInput";

export const Form = () => (
  <>
    <BookInput source="book" validate={required()}/>
    <ConditionInput source="condition" validate={required()}/>
  </>
);
