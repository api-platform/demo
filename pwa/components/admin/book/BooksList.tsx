import { FieldGuesser } from "@api-platform/admin";
import {
  TextInput,
  Datagrid,
  useRecordContext,
  List,
  EditButton,
  WrapperField,
  usePermissions,
} from "react-admin";
import { Card, CardContent } from "@mui/material";

import { ShowButton } from "./ShowButton";
import { RatingField } from "../review/RatingField";
import { ConditionInput } from "./ConditionInput";

const ConditionField = () => {
  const record = useRecordContext();
  if (!record || !record.condition) return null;
  return (
    <span>
      {record.condition.replace(/https:\/\/schema\.org\/(.+)Condition$/, "$1")}
    </span>
  );
};

const filters = [
  <TextInput source="title" key="title" />,
  <TextInput source="author" key="author" />,
  <ConditionInput source="condition" key="condition" />,
];

export const BooksList = () => {
  const { isPending, permissions } = usePermissions();

  if (isPending) {
    return <div>Waiting for permissions...</div>;
  }

  if (permissions !== 'admin') {
    return (
      <Card>
        <CardContent>You are not allowed to access this page.</CardContent>
      </Card>
    );
  }

  return (
    <List filters={filters} exporter={false} title="Books">
      <Datagrid>
        <FieldGuesser source="title"/>
        <FieldGuesser source="author" sortable={false}/>
        <WrapperField label="Condition" sortable={false}>
          <ConditionField/>
        </WrapperField>
        <WrapperField label="Rating" sortable={false}>
          <RatingField/>
        </WrapperField>
        <ShowButton/>
        <EditButton/>
      </Datagrid>
    </List>
  );
};
