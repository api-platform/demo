import { FieldGuesser, type ListGuesserProps } from "@api-platform/admin";
import {
  TextInput,
  Pagination,
  Datagrid,
  type PaginationProps,
  useRecordContext,
  type UseRecordContextParams,
  Button,
  List as ReactAdminList,
  EditButton, ShowButtonProps
} from "react-admin";
import VisibilityIcon from "@mui/icons-material/Visibility";
import slugify from "slugify";

import { getItemPath } from "@/utils/dataAccess";
import { RatingField } from "@/components/admin/review/RatingField";
import { ConditionInput } from "@/components/admin/book/ConditionInput";

const ConditionField = (props: UseRecordContextParams) => {
  const record = useRecordContext(props);

  // todo translate condition
  return !!record && !!record.condition ? <span>{record.condition.replace("https://schema.org/", "")}</span> : null;
};
ConditionField.defaultProps = { label: "Condition" };

const filters = [
  <TextInput source="title"/>,
  <TextInput source="author"/>,
  <ConditionInput source="condition"/>,
];

const PostPagination = (props: PaginationProps) => <Pagination rowsPerPageOptions={[]} {...props}/>;

const ShowButton = (props: ShowButtonProps) => {
  const record = useRecordContext(props);

  return record ? (
    // @ts-ignore
    <Button label="Show" target="_blank" href={getItemPath({
      id: record["@id"].replace(/^\/admin\/books\//, ""),
      slug: slugify(`${record.title}-${record.author}`, {lower: true, trim: true, remove: /[*+~.(),;'"!:@]/g}),
    }, "/books/[id]/[slug]")}>
      <VisibilityIcon/>
    </Button>
  ) : null;
};

export const List = (props: ListGuesserProps) => (
  <ReactAdminList {...props} filters={filters} pagination={<PostPagination/>} exporter={false} title="Books">
    <Datagrid>
      <FieldGuesser source="title"/>
      <FieldGuesser source="author"/>
      <ConditionField source="condition"/>
      <RatingField source="rating"/>
      <ShowButton/>
      <EditButton/>
    </Datagrid>
  </ReactAdminList>
);
