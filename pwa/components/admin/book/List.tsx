import { FieldGuesser, type ListGuesserProps } from "@api-platform/admin";
import {
  TextInput,
  Datagrid,
  useRecordContext,
  type UseRecordContextParams,
  List as ReactAdminList,
  EditButton,
} from "react-admin";

import { ShowButton } from "@/components/admin/book/ShowButton";
import { RatingField } from "@/components/admin/review/RatingField";
import { ConditionInput } from "@/components/admin/book/ConditionInput";

const ConditionField = (props: UseRecordContextParams) => {
  const record = useRecordContext(props);

  return !!record && !!record.condition ? <span>{record.condition.replace(/https:\/\/schema\.org\/(.+)Condition$/, "$1")}</span> : null;
};
ConditionField.defaultProps = { label: "Condition" };

const filters = [
  <TextInput source="title" key="title"/>,
  <TextInput source="author" key="author"/>,
  <ConditionInput source="condition" key="condition"/>,
];

export const List = (props: ListGuesserProps) => (
  <ReactAdminList {...props} filters={filters} exporter={false} title="Books">
    <Datagrid>
      <FieldGuesser source="title"/>
      <FieldGuesser source="author" sortable={false}/>
      <ConditionField source="condition" sortable={false}/>
      <RatingField source="rating" sortable={false}/>
      <ShowButton/>
      <EditButton/>
    </Datagrid>
  </ReactAdminList>
);
