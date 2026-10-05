import LabTestForm from './Form';

export default function LabTestEdit({ labTest, routes }) {
    return <LabTestForm mode="edit" labTest={labTest} routes={routes} />;
}
